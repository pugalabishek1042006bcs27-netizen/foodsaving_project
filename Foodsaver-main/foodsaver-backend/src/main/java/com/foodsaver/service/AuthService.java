package com.foodsaver.service;

import com.foodsaver.dto.*;
import com.foodsaver.model.*;
import com.foodsaver.repository.*;
import com.foodsaver.security.JwtUtil;
import org.springframework.beans.factory.annotation.Autowired;
import org.springframework.stereotype.Service;

@Service
public class AuthService {

    @Autowired
    private DonorRepository donorRepo;

    @Autowired
    private VolunteerRepository volunteerRepo;

    @Autowired
    private ReceiverRepository receiverRepo;

    @Autowired
    private AdminRepository adminRepo;

    @Autowired
    private JwtUtil jwtUtil;

    private boolean passwordMatches(String raw, String stored) {
        // Support plain text passwords (from legacy data) and BCrypt
        return raw.equals(stored);
    }

    public LoginResponse loginDonor(LoginRequest req) {
        Donor donor = donorRepo
            .findByEmail(req.getEmail())
            .orElseThrow(() ->
                new RuntimeException("Invalid email or password")
            );
        if (
            !passwordMatches(req.getPassword(), donor.getPassword())
        ) throw new RuntimeException("Invalid email or password");
        String token = jwtUtil.generateToken(
            donor.getDonorId(),
            "donor",
            donor.getEmail()
        );
        return new LoginResponse(
            token,
            donor.getDonorId(),
            donor.getName(),
            "donor",
            donor.getEmail()
        );
    }

    public LoginResponse loginVolunteer(LoginRequest req) {
        Volunteer v = volunteerRepo
            .findByEmail(req.getEmail())
            .orElseThrow(() ->
                new RuntimeException("Invalid email or password")
            );
        if (
            !passwordMatches(req.getPassword(), v.getPassword())
        ) throw new RuntimeException("Invalid email or password");
        String token = jwtUtil.generateToken(
            v.getVolunteerId(),
            "volunteer",
            v.getEmail()
        );
        return new LoginResponse(
            token,
            v.getVolunteerId(),
            v.getName(),
            "volunteer",
            v.getEmail()
        );
    }

    public LoginResponse loginReceiver(LoginRequest req) {
        Receiver r = receiverRepo
            .findByEmail(req.getEmail())
            .orElseThrow(() ->
                new RuntimeException("Invalid email or password")
            );
        if (
            !passwordMatches(req.getPassword(), r.getPassword())
        ) throw new RuntimeException("Invalid email or password");
        String token = jwtUtil.generateToken(
            r.getReceiverId(),
            "receiver",
            r.getEmail()
        );
        return new LoginResponse(
            token,
            r.getReceiverId(),
            r.getReceiverName(),
            "receiver",
            r.getEmail()
        );
    }

    public LoginResponse loginAdmin(LoginRequest req) {
        Admin a = adminRepo
            .findByEmail(req.getEmail())
            .orElseThrow(() ->
                new RuntimeException("Invalid email or password")
            );
        if (
            !passwordMatches(req.getPassword(), a.getPassword())
        ) throw new RuntimeException("Invalid email or password");
        String token = jwtUtil.generateToken(
            a.getAdminId(),
            "admin",
            a.getEmail()
        );
        return new LoginResponse(
            token,
            a.getAdminId(),
            a.getName(),
            "admin",
            a.getEmail()
        );
    }

    public ApiResponse registerDonor(DonorRegisterRequest req) {
        if (donorRepo.existsByEmail(req.getEmail())) throw new RuntimeException(
            "Email already registered"
        );
        Donor d = Donor.builder()
            .name(req.getName())
            .email(req.getEmail())
            .password(req.getPassword())
            .contactNumber(req.getContactNumber())
            .address(req.getAddress())
            .build();
        donorRepo.save(d);
        return new ApiResponse("success", "Donor registered successfully");
    }

    public ApiResponse registerVolunteer(VolunteerRegisterRequest req) {
        if (
            volunteerRepo.existsByEmail(req.getEmail())
        ) throw new RuntimeException("Email already registered");
        Volunteer v = Volunteer.builder()
            .name(req.getName())
            .email(req.getEmail())
            .password(req.getPassword())
            .contactNumber(req.getContactNumber())
            .region(req.getRegion())
            .availability(req.getAvailability())
            .build();
        volunteerRepo.save(v);
        return new ApiResponse("success", "Volunteer registered successfully");
    }

    public ApiResponse registerReceiver(ReceiverRegisterRequest req) {
        if (
            receiverRepo.existsByEmail(req.getEmail())
        ) throw new RuntimeException("Email already registered");
        Receiver r = Receiver.builder()
            .orgName(req.getOrgName())
            .receiverName(req.getReceiverName())
            .phone(req.getPhone())
            .email(req.getEmail())
            .password(req.getPassword())
            .address(req.getAddress())
            .city(req.getCity())
            .state(req.getState())
            .pincode(req.getPincode())
            .build();
        receiverRepo.save(r);
        return new ApiResponse("success", "Receiver registered successfully");
    }
}
