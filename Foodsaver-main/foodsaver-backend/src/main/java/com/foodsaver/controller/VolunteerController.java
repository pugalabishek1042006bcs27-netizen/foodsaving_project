package com.foodsaver.controller;

import com.foodsaver.model.*;
import com.foodsaver.repository.*;
import com.foodsaver.security.JwtUtil;
import com.foodsaver.service.*;
import java.util.Map;
import org.springframework.beans.factory.annotation.Autowired;
import org.springframework.http.ResponseEntity;
import org.springframework.web.bind.annotation.*;

@RestController
@RequestMapping("/api/volunteer")
public class VolunteerController {

    @Autowired
    private VolunteerService volunteerService;

    @Autowired
    private DonationService donationService;

    @Autowired
    private FoodDonationRepository donationRepo;

    @Autowired
    private DeliveryLogRepository deliveryRepo;

    @Autowired
    private VolunteerLocationRepository locationRepo;

    @Autowired
    private JwtUtil jwtUtil;

    private String getUserId(String authHeader) {
        if (authHeader == null || !authHeader.startsWith("Bearer ")) return null;
        return jwtUtil.extractUserId(authHeader.substring(7));
    }

    @GetMapping("/profile")
    public ResponseEntity<?> getProfile(
        @RequestHeader("Authorization") String auth
    ) {
        Volunteer v = volunteerService.getProfile(getUserId(auth));
        v.setPassword(null);
        return ResponseEntity.ok(v);
    }

    @PutMapping("/profile")
    public ResponseEntity<?> updateProfile(
        @RequestHeader("Authorization") String auth,
        @RequestBody Volunteer updated
    ) {
        Volunteer v = volunteerService.updateProfile(getUserId(auth), updated);
        v.setPassword(null);
        return ResponseEntity.ok(v);
    }

    @GetMapping("/available-donations")
    public ResponseEntity<?> getAvailableDonations() {
        return ResponseEntity.ok(donationRepo.findByStatus("pending"));
    }

    @PostMapping("/accept/{donationId}")
    public ResponseEntity<?> acceptDonation(
        @RequestHeader("Authorization") String auth,
        @PathVariable String donationId
    ) {
        try {
            return ResponseEntity.ok(
                donationService.acceptDonationByVolunteer(
                    donationId,
                    getUserId(auth)
                )
            );
        } catch (Exception e) {
            return ResponseEntity.badRequest().body(
                Map.of("error", e.getMessage())
            );
        }
    }

    @GetMapping("/my-deliveries")
    public ResponseEntity<?> getMyDeliveries(
        @RequestHeader("Authorization") String auth
    ) {
        return ResponseEntity.ok(
            deliveryRepo.findByVolunteerId(getUserId(auth))
        );
    }

    @PostMapping("/update-location")
    public ResponseEntity<?> updateLocation(
        @RequestHeader("Authorization") String auth,
        @RequestBody VolunteerLocation location
    ) {
        location.setVolunteerId(getUserId(auth));
        return ResponseEntity.ok(locationRepo.save(location));
    }
}
