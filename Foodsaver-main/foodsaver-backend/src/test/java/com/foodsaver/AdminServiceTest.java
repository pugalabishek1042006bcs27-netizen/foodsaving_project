package com.foodsaver;

import com.foodsaver.model.*;
import com.foodsaver.repository.*;
import com.foodsaver.service.AdminService;
import com.foodsaver.service.NotificationService;
import org.junit.jupiter.api.Test;
import org.junit.jupiter.api.extension.ExtendWith;
import org.mockito.ArgumentCaptor;
import org.mockito.InjectMocks;
import org.mockito.Mock;
import org.mockito.junit.jupiter.MockitoExtension;

import java.util.*;

import static org.junit.jupiter.api.Assertions.*;
import static org.mockito.ArgumentMatchers.any;
import static org.mockito.ArgumentMatchers.anyString;
import static org.mockito.ArgumentMatchers.eq;
import static org.mockito.Mockito.*;

@ExtendWith(MockitoExtension.class)
class AdminServiceTest {

    @Mock
    private DonorRepository donorRepo;

    @Mock
    private VolunteerRepository volunteerRepo;

    @Mock
    private ReceiverRepository receiverRepo;

    @Mock
    private FoodDonationRepository donationRepo;

    @Mock
    private CertificateRepository certRepo;

    @Mock
    private DeliveryLogRepository deliveryRepo;

    @Mock
    private NotificationService notifService;

    @InjectMocks
    private AdminService adminService;

    @Test
    void assignVolunteer_Success() {
        String donationId = "don-1";
        String volunteerId = "v-1";

        FoodDonation donation = FoodDonation.builder()
                .donationId(donationId)
                .donorId("d1")
                .foodType("Vegetables")
                .status("pending")
                .otpCode("123456")
                .build();

        when(donationRepo.findById(donationId)).thenReturn(Optional.of(donation));
        when(donationRepo.save(any(FoodDonation.class))).thenReturn(donation);

        FoodDonation result = adminService.assignVolunteer(donationId, volunteerId);

        assertEquals("accepted", result.getStatus());
        assertEquals(volunteerId, result.getVolunteerId());

        ArgumentCaptor<DeliveryLog> logCaptor = ArgumentCaptor.forClass(DeliveryLog.class);
        verify(deliveryRepo).save(logCaptor.capture());
        DeliveryLog savedLog = logCaptor.getValue();
        assertEquals(volunteerId, savedLog.getVolunteerId());
        assertEquals(donationId, savedLog.getDonationId());
        assertEquals("Assigned", savedLog.getDeliveryStatus());
        assertNotNull(savedLog.getCreatedAt());

        verify(notifService).send(eq("donor"), eq("d1"), anyString());
    }

    @Test
    void assignVolunteer_DonationNotFound_ThrowsException() {
        when(donationRepo.findById("invalid-id")).thenReturn(Optional.empty());

        RuntimeException ex = assertThrows(RuntimeException.class,
                () -> adminService.assignVolunteer("invalid-id", "v-1"));
        assertEquals("Donation not found", ex.getMessage());
    }

    @Test
    void getDashboardStats_ReturnsCorrectCounts() {
        when(donorRepo.count()).thenReturn(10L);
        when(volunteerRepo.count()).thenReturn(5L);
        when(receiverRepo.count()).thenReturn(8L);
        when(donationRepo.count()).thenReturn(50L);

        FoodDonation pending1 = FoodDonation.builder().donationId("1").status("pending").build();
        FoodDonation pending2 = FoodDonation.builder().donationId("2").status("pending").build();
        when(donationRepo.findByStatus("pending")).thenReturn(List.of(pending1, pending2));

        FoodDonation completed1 = FoodDonation.builder().donationId("3").status("completed").build();
        when(donationRepo.findByStatus("completed")).thenReturn(List.of(completed1));

        Certificate cert1 = Certificate.builder().certId("c1").status("Pending").build();
        when(certRepo.findByStatus("Pending")).thenReturn(List.of(cert1));

        Map<String, Object> stats = adminService.getDashboardStats();

        assertEquals(10L, stats.get("totalDonors"));
        assertEquals(5L, stats.get("totalVolunteers"));
        assertEquals(8L, stats.get("totalReceivers"));
        assertEquals(50L, stats.get("totalDonations"));
        assertEquals(2, stats.get("pendingDonations"));
        assertEquals(1, stats.get("completedDonations"));
        assertEquals(1, stats.get("pendingCertificates"));
    }
}
