package com.foodsaver.controller;

import com.foodsaver.dto.ApiResponse;
import com.foodsaver.model.*;
import com.foodsaver.repository.*;
import com.foodsaver.security.JwtUtil;
import com.foodsaver.service.*;
import java.nio.file.*;
import java.util.Map;
import org.springframework.beans.factory.annotation.Autowired;
import org.springframework.http.ResponseEntity;
import org.springframework.web.bind.annotation.*;
import org.springframework.web.multipart.MultipartFile;

@RestController
@RequestMapping("/api/receiver")
public class ReceiverController {

    @Autowired
    private ReceiverService receiverService;

    @Autowired
    private DonationService donationService;

    @Autowired
    private FoodRequestRepository requestRepo;

    @Autowired
    private CertificateRepository certRepo;

    @Autowired
    private JwtUtil jwtUtil;

    private String getUserId(String authHeader) {
        return jwtUtil.extractUserId(authHeader.substring(7));
    }

    @GetMapping("/profile")
    public ResponseEntity<?> getProfile(
        @RequestHeader("Authorization") String auth
    ) {
        Receiver r = receiverService.getProfile(getUserId(auth));
        r.setPassword(null);
        return ResponseEntity.ok(r);
    }

    @PutMapping("/profile")
    public ResponseEntity<?> updateProfile(
        @RequestHeader("Authorization") String auth,
        @RequestBody Receiver updated
    ) {
        Receiver r = receiverService.updateProfile(getUserId(auth), updated);
        r.setPassword(null);
        return ResponseEntity.ok(r);
    }

    @PostMapping("/request/{donationId}")
    public ResponseEntity<?> requestDonation(
        @RequestHeader("Authorization") String auth,
        @PathVariable String donationId,
        @RequestBody Map<String, Object> body
    ) {
        try {
            String details = (String) body.getOrDefault("details", "");
            Integer qty = (Integer) body.getOrDefault("quantity", 1);
            return ResponseEntity.ok(
                donationService.requestDonation(
                    getUserId(auth),
                    donationId,
                    details,
                    qty
                )
            );
        } catch (Exception e) {
            return ResponseEntity.badRequest().body(
                new ApiResponse("error", e.getMessage())
            );
        }
    }

    @GetMapping("/my-requests")
    public ResponseEntity<?> getMyRequests(
        @RequestHeader("Authorization") String auth
    ) {
        return ResponseEntity.ok(requestRepo.findByReceiverId(getUserId(auth)));
    }

    @PostMapping("/upload-certificate")
    public ResponseEntity<?> uploadCertificate(
        @RequestHeader("Authorization") String auth,
        @RequestParam("file") MultipartFile file
    ) {
        try {
            String receiverId = getUserId(auth);
            Path uploadPath = Paths.get("./uploads/certificates");
            if (!Files.exists(uploadPath)) Files.createDirectories(uploadPath);
            String filename =
                System.currentTimeMillis() + "_" + file.getOriginalFilename();
            Files.copy(
                file.getInputStream(),
                uploadPath.resolve(filename),
                StandardCopyOption.REPLACE_EXISTING
            );
            Certificate cert = Certificate.builder()
                .receiverId(receiverId)
                .filePath("uploads/certificates/" + filename)
                .status("Pending")
                .build();
            certRepo.save(cert);
            return ResponseEntity.ok(
                new ApiResponse("success", "Certificate uploaded")
            );
        } catch (Exception e) {
            return ResponseEntity.badRequest().body(
                new ApiResponse("error", e.getMessage())
            );
        }
    }
}
