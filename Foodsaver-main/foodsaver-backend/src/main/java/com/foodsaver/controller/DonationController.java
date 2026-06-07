package com.foodsaver.controller;

import com.foodsaver.dto.ApiResponse;
import com.foodsaver.repository.FoodDonationRepository;
import com.foodsaver.security.JwtUtil;
import com.foodsaver.service.DonationService;
import java.util.List;
import java.util.Map;
import org.springframework.beans.factory.annotation.Autowired;
import org.springframework.http.ResponseEntity;
import org.springframework.web.bind.annotation.*;
import org.springframework.web.multipart.MultipartFile;

@RestController
@RequestMapping("/api/donations")
public class DonationController {

    @Autowired
    private DonationService donationService;

    @Autowired
    private FoodDonationRepository donationRepo;

    @Autowired
    private JwtUtil jwtUtil;

    private Long getUserId(String authHeader) {
        return jwtUtil.extractUserId(authHeader.substring(7));
    }

    @PostMapping("/upload")
    public ResponseEntity<?> uploadDonation(
        @RequestHeader("Authorization") String auth,
        @RequestParam Map<String, String> fields,
        @RequestParam(value = "images", required = false) List<
            MultipartFile
        > images
    ) {
        try {
            return ResponseEntity.ok(
                donationService.uploadDonation(getUserId(auth), fields, images)
            );
        } catch (Exception e) {
            return ResponseEntity.badRequest().body(
                new ApiResponse("error", e.getMessage())
            );
        }
    }

    @GetMapping("/available")
    public ResponseEntity<?> getAvailable() {
        return ResponseEntity.ok(donationRepo.findByStatus("pending"));
    }

    @GetMapping("/{id}")
    public ResponseEntity<?> getDonation(@PathVariable Long id) {
        return donationRepo
            .findById(id)
            .map(ResponseEntity::ok)
            .orElse(ResponseEntity.notFound().build());
    }

    @PostMapping("/verify-pickup")
    public ResponseEntity<?> verifyPickup(
        @RequestHeader("Authorization") String auth,
        @RequestBody Map<String, Object> body
    ) {
        try {
            Long donationId = Long.parseLong(body.get("donationId").toString());
            String otp = (String) body.get("otp");
            return ResponseEntity.ok(
                donationService.verifyPickup(donationId, otp, getUserId(auth))
            );
        } catch (Exception e) {
            return ResponseEntity.badRequest().body(
                new ApiResponse("error", e.getMessage())
            );
        }
    }

    @PostMapping("/verify-delivery")
    public ResponseEntity<?> verifyDelivery(
        @RequestHeader("Authorization") String auth,
        @RequestBody Map<String, Object> body
    ) {
        try {
            Long donationId = Long.parseLong(body.get("donationId").toString());
            String otp = (String) body.get("receiverOtp");
            return ResponseEntity.ok(
                donationService.verifyDelivery(donationId, getUserId(auth), otp)
            );
        } catch (Exception e) {
            return ResponseEntity.badRequest().body(
                new ApiResponse("error", e.getMessage())
            );
        }
    }
}
