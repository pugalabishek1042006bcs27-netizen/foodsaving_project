package com.foodsaver.controller;

import com.foodsaver.dto.ApiResponse;
import com.foodsaver.repository.*;
import com.foodsaver.service.AdminService;
import java.util.Map;
import org.springframework.beans.factory.annotation.Autowired;
import org.springframework.http.ResponseEntity;
import org.springframework.web.bind.annotation.*;

@RestController
@RequestMapping("/api/admin")
public class AdminController {

    @Autowired
    private AdminService adminService;

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
    private ContactMessageRepository contactRepo;

    @Autowired
    private VolunteerLocationRepository locationRepo;

    @GetMapping("/dashboard")
    public ResponseEntity<?> getDashboard() {
        return ResponseEntity.ok(adminService.getDashboardStats());
    }

    @GetMapping("/donors")
    public ResponseEntity<?> getAllDonors() {
        return ResponseEntity.ok(donorRepo.findAll());
    }

    @GetMapping("/volunteers")
    public ResponseEntity<?> getAllVolunteers() {
        return ResponseEntity.ok(volunteerRepo.findAll());
    }

    @GetMapping("/receivers")
    public ResponseEntity<?> getAllReceivers() {
        return ResponseEntity.ok(receiverRepo.findAll());
    }

    @GetMapping("/donations")
    public ResponseEntity<?> getAllDonations() {
        return ResponseEntity.ok(donationRepo.findAll());
    }

    @PutMapping("/donations/{id}/status")
    public ResponseEntity<?> updateDonationStatus(
        @PathVariable Long id,
        @RequestBody Map<String, String> body
    ) {
        try {
            return ResponseEntity.ok(
                adminService.updateDonationStatus(id, body.get("status"))
            );
        } catch (Exception e) {
            return ResponseEntity.badRequest().body(
                new ApiResponse("error", e.getMessage())
            );
        }
    }

    @PostMapping("/accept-donation/{donationId}/{volunteerId}")
    public ResponseEntity<?> assignVolunteer(
        @PathVariable Long donationId,
        @PathVariable Long volunteerId
    ) {
        try {
            return ResponseEntity.ok(
                adminService.assignVolunteer(donationId, volunteerId)
            );
        } catch (Exception e) {
            return ResponseEntity.badRequest().body(
                new ApiResponse("error", e.getMessage())
            );
        }
    }

    @GetMapping("/certificates")
    public ResponseEntity<?> getCertificates() {
        return ResponseEntity.ok(certRepo.findAll());
    }

    @PutMapping("/certificates/{id}/status")
    public ResponseEntity<?> updateCertificate(
        @PathVariable Long id,
        @RequestBody Map<String, String> body
    ) {
        try {
            return ResponseEntity.ok(
                adminService.updateCertificateStatus(id, body.get("status"))
            );
        } catch (Exception e) {
            return ResponseEntity.badRequest().body(
                new ApiResponse("error", e.getMessage())
            );
        }
    }

    @GetMapping("/contact-messages")
    public ResponseEntity<?> getContactMessages() {
        return ResponseEntity.ok(contactRepo.findAll());
    }

    @GetMapping("/volunteer-locations")
    public ResponseEntity<?> getVolunteerLocations() {
        return ResponseEntity.ok(locationRepo.findAll());
    }
}
