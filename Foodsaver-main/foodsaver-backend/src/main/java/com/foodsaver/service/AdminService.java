package com.foodsaver.service;

import com.foodsaver.model.*;
import com.foodsaver.repository.*;
import java.util.HashMap;
import java.util.Map;
import org.springframework.beans.factory.annotation.Autowired;
import org.springframework.stereotype.Service;

@Service
public class AdminService {

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

    public Map<String, Object> getDashboardStats() {
        Map<String, Object> stats = new HashMap<>();
        stats.put("totalDonors", donorRepo.count());
        stats.put("totalVolunteers", volunteerRepo.count());
        stats.put("totalReceivers", receiverRepo.count());
        stats.put("totalDonations", donationRepo.count());
        stats.put(
            "pendingDonations",
            donationRepo.findByStatus("pending").size()
        );
        stats.put(
            "completedDonations",
            donationRepo.findByStatus("completed").size()
        );
        stats.put(
            "pendingCertificates",
            certRepo.findByStatus("Pending").size()
        );
        return stats;
    }

    public FoodDonation assignVolunteer(Long donationId, Long volunteerId) {
        FoodDonation d = donationRepo
            .findById(donationId)
            .orElseThrow(() -> new RuntimeException("Donation not found"));
        d.setVolunteerId(volunteerId);
        d.setStatus("accepted");
        return donationRepo.save(d);
    }

    public Certificate updateCertificateStatus(Long certId, String status) {
        Certificate c = certRepo
            .findById(certId)
            .orElseThrow(() -> new RuntimeException("Certificate not found"));
        c.setStatus(status);
        return certRepo.save(c);
    }

    public FoodDonation updateDonationStatus(Long donationId, String status) {
        FoodDonation d = donationRepo
            .findById(donationId)
            .orElseThrow(() -> new RuntimeException("Donation not found"));
        d.setStatus(status);
        return donationRepo.save(d);
    }
}
