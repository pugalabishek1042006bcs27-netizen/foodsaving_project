package com.foodsaver.controller;

import com.foodsaver.dto.*;
import com.foodsaver.service.AuthService;
import jakarta.validation.Valid;
import org.springframework.beans.factory.annotation.Autowired;
import org.springframework.http.ResponseEntity;
import org.springframework.web.bind.annotation.*;

@RestController
@RequestMapping("/api/auth")
public class AuthController {

    @Autowired
    private AuthService authService;

    @PostMapping("/login/donor")
    public ResponseEntity<?> loginDonor(@Valid @RequestBody LoginRequest req) {
        try {
            return ResponseEntity.ok(authService.loginDonor(req));
        } catch (Exception e) {
            return ResponseEntity.badRequest().body(
                new ApiResponse("error", e.getMessage())
            );
        }
    }

    @PostMapping("/login/volunteer")
    public ResponseEntity<?> loginVolunteer(@Valid @RequestBody LoginRequest req) {
        try {
            return ResponseEntity.ok(authService.loginVolunteer(req));
        } catch (Exception e) {
            return ResponseEntity.badRequest().body(
                new ApiResponse("error", e.getMessage())
            );
        }
    }

    @PostMapping("/login/receiver")
    public ResponseEntity<?> loginReceiver(@Valid @RequestBody LoginRequest req) {
        try {
            return ResponseEntity.ok(authService.loginReceiver(req));
        } catch (Exception e) {
            return ResponseEntity.badRequest().body(
                new ApiResponse("error", e.getMessage())
            );
        }
    }

    @PostMapping("/login/admin")
    public ResponseEntity<?> loginAdmin(@Valid @RequestBody LoginRequest req) {
        try {
            return ResponseEntity.ok(authService.loginAdmin(req));
        } catch (Exception e) {
            return ResponseEntity.badRequest().body(
                new ApiResponse("error", e.getMessage())
            );
        }
    }

    @PostMapping("/register/donor")
    public ResponseEntity<?> registerDonor(
        @Valid @RequestBody DonorRegisterRequest req
    ) {
        try {
            return ResponseEntity.ok(authService.registerDonor(req));
        } catch (Exception e) {
            return ResponseEntity.badRequest().body(
                new ApiResponse("error", e.getMessage())
            );
        }
    }

    @PostMapping("/register/volunteer")
    public ResponseEntity<?> registerVolunteer(
        @Valid @RequestBody VolunteerRegisterRequest req
    ) {
        try {
            return ResponseEntity.ok(authService.registerVolunteer(req));
        } catch (Exception e) {
            return ResponseEntity.badRequest().body(
                new ApiResponse("error", e.getMessage())
            );
        }
    }

    @PostMapping("/register/receiver")
    public ResponseEntity<?> registerReceiver(
        @Valid @RequestBody ReceiverRegisterRequest req
    ) {
        try {
            return ResponseEntity.ok(authService.registerReceiver(req));
        } catch (Exception e) {
            return ResponseEntity.badRequest().body(
                new ApiResponse("error", e.getMessage())
            );
        }
    }
}
