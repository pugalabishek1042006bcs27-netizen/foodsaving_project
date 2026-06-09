package com.foodsaver.service;

import com.foodsaver.exception.ResourceNotFoundException;
import com.foodsaver.model.*;
import com.foodsaver.repository.*;
import java.time.LocalDateTime;
import java.util.HashMap;
import java.util.Map;
import org.springframework.beans.factory.annotation.Autowired;
import org.springframework.stereotype.Service;

@Service
public class AdminService {

    @Autowired
    private DonorRepository donorRepo;

    @Autowired
    private VolunteerRepository volunteerRepo;

    @Autowired
    private ReceiverRepository receiverRepo;

    @Autowired
    private FoodDonationRepository donationRepo;

    @Autowired
    private CertificateRepository certRepo;

    @Autowired
    private DeliveryLogRepository deliveryRepo;

    @Autowired
    private NotificationService notifService;

    public Map<String, Object> getDashboardStats() {
        Map<String, Object> stats = new HashMap<>();
        stats.put("totalDonors", donorRepo.count());
        stats.put("totalVolunteers", volunteerRepo.count());
        stats.put("totalReceivers", receiverRepo.count());
        stats.put("totalDonations", donationRepo.count());
        stats.put(
            "pendingDonations",
            donationRepo.findByStatus("pending").size()
        );
        stats.put(
            "completedDonations",
            donationRepo.findByStatus("completed").size()
        );
        stats.put(
            "pendingCertificates",
            certRepo.findByStatus("Pending").size()
        );
        return stats;
    }

    public FoodDonation assignVolunteer(String donationId, String volunteerId) {
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
            .createdAt(LocalDateTime.now())
            .build();
        deliveryRepo.save(log);

        notifService.send(
            "donor",
            d.getDonorId(),
            "Your donation (ID: " + donationId + ") has been assigned to a volunteer."
        );

        return d;
    }

    public Certificate updateCertificateStatus(String certId, String status) {
        Certificate c = certRepo
            .findById(certId)
            .orElseThrow(() -> new ResourceNotFoundException("Certificate not found"));
        c.setStatus(status);
        return certRepo.save(c);
    }

    public FoodDonation updateDonationStatus(String donationId, String status) {
        FoodDonation d = donationRepo
            .findById(donationId)
            .orElseThrow(() -> new ResourceNotFoundException("Donation not found"));
        d.setStatus(status);
        return donationRepo.save(d);
    }
}
