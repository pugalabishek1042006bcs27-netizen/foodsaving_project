package com.foodsaver;

import com.foodsaver.dto.*;
import com.foodsaver.model.Donor;
import com.foodsaver.repository.DonorRepository;
import com.foodsaver.repository.VolunteerRepository;
import com.foodsaver.repository.ReceiverRepository;
import com.foodsaver.repository.AdminRepository;
import com.foodsaver.security.JwtUtil;
import com.foodsaver.service.AuthService;
import org.junit.jupiter.api.Test;
import org.junit.jupiter.api.extension.ExtendWith;
import org.mockito.InjectMocks;
import org.mockito.Mock;
import org.mockito.junit.jupiter.MockitoExtension;
import org.springframework.security.crypto.password.PasswordEncoder;

import java.util.Optional;

import static org.junit.jupiter.api.Assertions.*;
import static org.mockito.ArgumentMatchers.any;
import static org.mockito.Mockito.*;

@ExtendWith(MockitoExtension.class)
class AuthServiceTest {

    @Mock
    private DonorRepository donorRepo;

    @Mock
    private VolunteerRepository volunteerRepo;

    @Mock
    private ReceiverRepository receiverRepo;

    @Mock
    private AdminRepository adminRepo;

    @Mock
    private JwtUtil jwtUtil;

    @Mock
    private PasswordEncoder passwordEncoder;

    @InjectMocks
    private AuthService authService;

    @Test
    void registerDonor_Success() {
        DonorRegisterRequest req = new DonorRegisterRequest();
        req.setName("John");
        req.setEmail("john@test.com");
        req.setPassword("pass123");
        req.setContactNumber("1234567890");
        req.setAddress("123 Street");

        when(passwordEncoder.encode(req.getPassword())).thenReturn("encodedPass123");
        when(donorRepo.existsByEmail(req.getEmail())).thenReturn(false);

        Donor savedDonor = Donor.builder()
                .name(req.getName())
                .email(req.getEmail())
                .password("encodedPass123")
                .contactNumber(req.getContactNumber())
                .address(req.getAddress())
                .build();
        when(donorRepo.save(any(Donor.class))).thenReturn(savedDonor);

        ApiResponse response = authService.registerDonor(req);

        assertEquals("success", response.getStatus());
        assertEquals("Donor registered successfully", response.getMessage());
        verify(donorRepo).save(any(Donor.class));
    }

    @Test
    void registerDonor_DuplicateEmail_ThrowsException() {
        DonorRegisterRequest req = new DonorRegisterRequest();
        req.setEmail("existing@test.com");

        when(donorRepo.existsByEmail(req.getEmail())).thenReturn(true);

        RuntimeException ex = assertThrows(RuntimeException.class,
                () -> authService.registerDonor(req));
        assertEquals("Email already registered", ex.getMessage());
        verify(donorRepo, never()).save(any());
    }

    @Test
    void loginDonor_Success() {
        LoginRequest req = new LoginRequest();
        req.setEmail("john@test.com");
        req.setPassword("pass123");

        Donor donor = Donor.builder()
                .donorId("d1")
                .name("John")
                .email("john@test.com")
                .password("pass123")
                .build();

        when(passwordEncoder.matches(req.getPassword(), donor.getPassword())).thenReturn(true);
        when(donorRepo.findByEmail(req.getEmail())).thenReturn(Optional.of(donor));
        when(jwtUtil.generateToken("d1", "donor", "john@test.com")).thenReturn("token123");

        LoginResponse response = authService.loginDonor(req);

        assertEquals("token123", response.getToken());
        assertEquals("d1", response.getUserId());
        assertEquals("John", response.getName());
        assertEquals("donor", response.getUserType());
        assertEquals("john@test.com", response.getEmail());
    }

    @Test
    void loginDonor_WrongPassword_ThrowsException() {
        LoginRequest req = new LoginRequest();
        req.setEmail("john@test.com");
        req.setPassword("wrongpass");

        Donor donor = Donor.builder()
                .donorId("d1")
                .email("john@test.com")
                .password("pass123")
                .build();

        when(passwordEncoder.matches(req.getPassword(), donor.getPassword())).thenReturn(false);
        when(donorRepo.findByEmail(req.getEmail())).thenReturn(Optional.of(donor));

        RuntimeException ex = assertThrows(RuntimeException.class,
                () -> authService.loginDonor(req));
        assertEquals("Invalid email or password", ex.getMessage());
    }
}
