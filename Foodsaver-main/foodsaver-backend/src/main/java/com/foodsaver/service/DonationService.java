package com.foodsaver.service;

import com.foodsaver.exception.InvalidOtpException;
import com.foodsaver.exception.ResourceNotFoundException;
import com.foodsaver.model.*;
import com.foodsaver.repository.*;
import java.io.IOException;
import java.nio.file.*;
import java.time.LocalDate;
import java.time.LocalDateTime;
import java.util.*;
import org.springframework.beans.factory.annotation.Autowired;
import org.springframework.beans.factory.annotation.Value;
import org.springframework.stereotype.Service;
import org.springframework.web.multipart.MultipartFile;

@Service
public class DonationService {

    @Autowired
    private FoodDonationRepository donationRepo;

    @Autowired
    private FoodRequestRepository requestRepo;

    @Autowired
    private DeliveryLogRepository deliveryRepo;

    @Autowired
    private NotificationService notifService;

    @Value("${file.upload-dir}")
    private String uploadDir;

    public FoodDonation uploadDonation(
        String donorId,
        Map<String, String> fields,
        List<MultipartFile> images
    ) throws IOException {
        List<String> imagePaths = new ArrayList<>();
        Path uploadPath = Paths.get(uploadDir);
        if (!Files.exists(uploadPath)) Files.createDirectories(uploadPath);

        if (images != null) {
            for (MultipartFile file : images) {
                if (!file.isEmpty()) {
                    String ext = getExtension(file.getOriginalFilename());
                    String filename =
                        System.currentTimeMillis() +
                        "_" +
                        UUID.randomUUID().toString().substring(0, 8) +
                        "." +
                        ext;
                    Files.copy(
                        file.getInputStream(),
                        uploadPath.resolve(filename),
                        StandardCopyOption.REPLACE_EXISTING
                    );
                    imagePaths.add("uploads/" + filename);
                }
            }
        }

        String otp = String.valueOf(new Random().nextInt(900000) + 100000);
        String expiryStr = fields.get("expiryDate");

        FoodDonation donation = FoodDonation.builder()
            .donorId(donorId)
            .foodType(fields.getOrDefault("foodType", ""))
            .quantity(fields.getOrDefault("quantity", ""))
            .expiryDate(
                expiryStr != null && !expiryStr.isEmpty()
                    ? LocalDate.parse(expiryStr)
                    : null
            )
            .preparationStatus(fields.getOrDefault("preparationStatus", ""))
            .dietaryOptions(fields.getOrDefault("dietaryOptions", ""))
            .allergens(fields.getOrDefault("allergens", ""))
            .description(fields.getOrDefault("description", ""))
            .contactPhone(fields.getOrDefault("contactPhone", ""))
            .contactEmail(fields.getOrDefault("contactEmail", ""))
            .address(fields.getOrDefault("address", ""))
            .imagePaths(String.join(",", imagePaths))
            .otpCode(otp)
            .status("pending")
            .uploadDate(LocalDateTime.now())
            .build();

        donation = donationRepo.save(donation);

        String msg =
            "New donation uploaded (#" +
            donation.getDonationId() +
            "): " +
            donation.getFoodType() +
            " — " +
            donation.getQuantity();
        notifService.broadcast("volunteer", msg);
        notifService.broadcast("receiver", msg);

        return donation;
    }

    public FoodDonation acceptDonationByVolunteer(
        String donationId,
        String volunteerId
    ) {
        FoodDonation d = donationRepo
            .findById(donationId)
            .orElseThrow(() -> new ResourceNotFoundException("Donation not found"));
        d.setVolunteerId(volunteerId);
        d.setStatus("accepted");
        donationRepo.save(d);

        DeliveryLog log = DeliveryLog.builder()
            .volunteerId(volunteerId)
            .donationId(donationId)
            .deliveryStatus("Assigned")
            .otpCode(d.getOtpCode())
            .build();
        deliveryRepo.save(log);

        notifService.send(
            "donor",
            d.getDonorId(),
            "Your donation (ID: " +
                donationId +
                ") has been accepted by a volunteer."
        );
        return d;
    }

    public FoodDonation verifyPickup(
        String donationId,
        String otp,
        String volunteerId
    ) {
        FoodDonation d = donationRepo
            .findById(donationId)
            .orElseThrow(() -> new ResourceNotFoundException("Donation not found"));
        if (!d.getOtpCode().equals(otp)) throw new InvalidOtpException(
            "Invalid OTP"
        );

        d.setStatus("in_progress");
        donationRepo.save(d);

        deliveryRepo
            .findByDonationId(donationId)
            .stream()
            .filter(l -> l.getVolunteerId().equals(volunteerId))
            .findFirst()
            .ifPresent(l -> {
                l.setDeliveryStatus("Picked Up");
                l.setPickedAt(LocalDateTime.now());
                l.setDonorVerified(true);
                deliveryRepo.save(l);
            });

        notifService.send(
            "donor",
            d.getDonorId(),
            "Your donation (ID: " +
                donationId +
                ") has been picked up by a volunteer."
        );
        return d;
    }

    public FoodDonation verifyDelivery(
        String donationId,
        String receiverId,
        String receiverOtp
    ) {
        FoodRequest req = requestRepo
            .findByDonationId(donationId)
            .stream()
            .filter(r -> r.getReceiverId().equals(receiverId))
            .findFirst()
            .orElseThrow(() -> new ResourceNotFoundException("Request not found"));

        if (
            !req.getReceiverOtp().equals(receiverOtp)
        ) throw new InvalidOtpException("Invalid OTP");

        req.setStatus("Completed");
        requestRepo.save(req);

        FoodDonation d = donationRepo
            .findById(donationId)
            .orElseThrow(() -> new ResourceNotFoundException("Donation not found"));
        d.setStatus("completed");
        donationRepo.save(d);

        deliveryRepo.findByDonationId(donationId).forEach(l -> {
            l.setDeliveryStatus("Delivered");
            l.setDeliveredAt(LocalDateTime.now());
            deliveryRepo.save(l);
        });

        notifService.send(
            "receiver",
            receiverId,
            "Your food request (Donation ID: " +
                donationId +
                ") has been delivered!"
        );
        notifService.send(
            "donor",
            d.getDonorId(),
            "Your donation (ID: " + donationId + ") was delivered successfully!"
        );
        return d;
    }

    public FoodRequest requestDonation(
        String receiverId,
        String donationId,
        String details,
        Integer qty
    ) {
        String otp = String.valueOf(new Random().nextInt(9000) + 1000);
        FoodRequest req = FoodRequest.builder()
            .receiverId(receiverId)
            .donationId(donationId)
            .details(details)
            .quantity(qty)
            .status("Pending")
            .receiverOtp(otp)
            .build();
        FoodRequest saved = requestRepo.save(req);

        FoodDonation d = donationRepo
            .findById(donationId)
            .orElseThrow(() -> new ResourceNotFoundException("Donation not found"));
        notifService.send(
            "donor",
            d.getDonorId(),
            "A receiver has requested your donation (ID: " + donationId + ")."
        );
        return saved;
    }

    private String getExtension(String filename) {
        if (filename == null || !filename.contains(".")) return "jpg";
        return filename.substring(filename.lastIndexOf('.') + 1);
    }
}
