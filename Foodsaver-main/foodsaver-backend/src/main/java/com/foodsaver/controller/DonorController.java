package com.foodsaver.controller;

import com.foodsaver.model.Donor;
import com.foodsaver.repository.FoodDonationRepository;
import com.foodsaver.security.JwtUtil;
import com.foodsaver.service.DonorService;
import org.springframework.beans.factory.annotation.Autowired;
import org.springframework.http.ResponseEntity;
import org.springframework.web.bind.annotation.*;

@RestController
@RequestMapping("/api/donor")
public class DonorController {

    @Autowired
    private DonorService donorService;

    @Autowired
    private FoodDonationRepository donationRepo;

    @Autowired
    private JwtUtil jwtUtil;

    private String getUserId(String authHeader) {
        return jwtUtil.extractUserId(authHeader.substring(7));
    }

    @GetMapping("/profile")
    public ResponseEntity<?> getProfile(
        @RequestHeader("Authorization") String auth
    ) {
        Donor d = donorService.getProfile(getUserId(auth));
        d.setPassword(null);
        return ResponseEntity.ok(d);
    }

    @PutMapping("/profile")
    public ResponseEntity<?> updateProfile(
        @RequestHeader("Authorization") String auth,
        @RequestBody Donor updated
    ) {
        Donor d = donorService.updateProfile(getUserId(auth), updated);
        d.setPassword(null);
        return ResponseEntity.ok(d);
    }

    @GetMapping("/donations")
    public ResponseEntity<?> getMyDonations(
        @RequestHeader("Authorization") String auth
    ) {
        return ResponseEntity.ok(donationRepo.findByDonorId(getUserId(auth)));
    }
}
