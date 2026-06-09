package com.foodsaver;

import com.foodsaver.model.*;
import com.foodsaver.repository.*;
import com.foodsaver.service.DonationService;
import com.foodsaver.service.NotificationService;
import org.junit.jupiter.api.Test;
import org.junit.jupiter.api.extension.ExtendWith;
import org.junit.jupiter.api.io.TempDir;
import org.mockito.ArgumentCaptor;
import org.mockito.InjectMocks;
import org.mockito.Mock;
import org.mockito.junit.jupiter.MockitoExtension;
import org.springframework.test.util.ReflectionTestUtils;
import org.springframework.web.multipart.MultipartFile;

import java.io.ByteArrayInputStream;
import java.io.IOException;
import java.nio.file.Path;
import java.util.*;

import static org.junit.jupiter.api.Assertions.*;
import static org.mockito.ArgumentMatchers.any;
import static org.mockito.ArgumentMatchers.anyString;
import static org.mockito.ArgumentMatchers.eq;
import static org.mockito.Mockito.*;

@ExtendWith(MockitoExtension.class)
class DonationServiceTest {

    @Mock
    private FoodDonationRepository donationRepo;

    @Mock
    private FoodRequestRepository requestRepo;

    @Mock
    private DeliveryLogRepository deliveryRepo;

    @Mock
    private NotificationService notifService;

    @InjectMocks
    private DonationService donationService;

    @TempDir
    Path tempDir;

    @Test
    void uploadDonation_Success() throws IOException {
        ReflectionTestUtils.setField(donationService, "uploadDir", tempDir.toString());

        Map<String, String> fields = new HashMap<>();
        fields.put("foodType", "Vegetables");
        fields.put("quantity", "10 kg");
        fields.put("expiryDate", "2026-06-15");
        fields.put("description", "Fresh vegetables");

        MultipartFile mockFile = mock(MultipartFile.class);
        when(mockFile.isEmpty()).thenReturn(false);
        when(mockFile.getOriginalFilename()).thenReturn("photo.jpg");
        when(mockFile.getInputStream()).thenReturn(new ByteArrayInputStream("test".getBytes()));

        List<MultipartFile> images = List.of(mockFile);

        FoodDonation savedDonation = FoodDonation.builder()
                .donationId("don-1")
                .donorId("d1")
                .foodType("Vegetables")
                .quantity("10 kg")
                .status("pending")
                .otpCode("123456")
                .build();

        when(donationRepo.save(any(FoodDonation.class))).thenReturn(savedDonation);

        FoodDonation result = donationService.uploadDonation("d1", fields, images);

        assertNotNull(result);
        assertEquals("pending", result.getStatus());
        assertNotNull(result.getOtpCode());
        assertEquals("Vegetables", result.getFoodType());
        assertEquals("10 kg", result.getQuantity());

        verify(notifService).broadcast(eq("volunteer"), anyString());
        verify(notifService).broadcast(eq("receiver"), anyString());
    }

    @Test
    void acceptDonationByVolunteer_Success() {
        String donationId = "don-1";
        String volunteerId = "v-1";

        FoodDonation donation = FoodDonation.builder()
                .donationId(donationId)
                .donorId("d1")
                .foodType("Vegetables")
                .quantity("10 kg")
                .status("pending")
                .otpCode("123456")
                .build();

        when(donationRepo.findById(donationId)).thenReturn(Optional.of(donation));
        when(donationRepo.save(any(FoodDonation.class))).thenReturn(donation);

        FoodDonation result = donationService.acceptDonationByVolunteer(donationId, volunteerId);

        assertEquals("accepted", result.getStatus());
        assertEquals(volunteerId, result.getVolunteerId());

        ArgumentCaptor<DeliveryLog> logCaptor = ArgumentCaptor.forClass(DeliveryLog.class);
        verify(deliveryRepo).save(logCaptor.capture());
        DeliveryLog savedLog = logCaptor.getValue();
        assertEquals(volunteerId, savedLog.getVolunteerId());
        assertEquals(donationId, savedLog.getDonationId());
        assertEquals("Assigned", savedLog.getDeliveryStatus());
        assertEquals("123456", savedLog.getOtpCode());

        verify(notifService).send(eq("donor"), eq("d1"), anyString());
    }

    @Test
    void verifyPickup_ValidOtp_Success() {
        String donationId = "don-1";
        String otp = "123456";
        String volunteerId = "v-1";

        FoodDonation donation = FoodDonation.builder()
                .donationId(donationId)
                .donorId("d1")
                .status("accepted")
                .otpCode("123456")
                .build();

        DeliveryLog deliveryLog = DeliveryLog.builder()
                .deliveryId("dl-1")
                .volunteerId(volunteerId)
                .donationId(donationId)
                .deliveryStatus("Assigned")
                .otpCode(otp)
                .build();

        when(donationRepo.findById(donationId)).thenReturn(Optional.of(donation));
        when(deliveryRepo.findByDonationId(donationId)).thenReturn(List.of(deliveryLog));
        when(donationRepo.save(any(FoodDonation.class))).thenReturn(donation);

        FoodDonation result = donationService.verifyPickup(donationId, otp, volunteerId);

        assertEquals("in_progress", result.getStatus());

        verify(deliveryRepo).save(deliveryLog);
        assertEquals("Picked Up", deliveryLog.getDeliveryStatus());
        assertTrue(deliveryLog.getDonorVerified());
        assertNotNull(deliveryLog.getPickedAt());

        verify(notifService).send(eq("donor"), eq("d1"), anyString());
    }

    @Test
    void verifyPickup_InvalidOtp_ThrowsException() {
        String donationId = "don-1";
        String wrongOtp = "000000";
        String volunteerId = "v-1";

        FoodDonation donation = FoodDonation.builder()
                .donationId(donationId)
                .donorId("d1")
                .otpCode("123456")
                .build();

        when(donationRepo.findById(donationId)).thenReturn(Optional.of(donation));

        RuntimeException ex = assertThrows(RuntimeException.class,
                () -> donationService.verifyPickup(donationId, wrongOtp, volunteerId));
        assertEquals("Invalid OTP", ex.getMessage());
    }
}
