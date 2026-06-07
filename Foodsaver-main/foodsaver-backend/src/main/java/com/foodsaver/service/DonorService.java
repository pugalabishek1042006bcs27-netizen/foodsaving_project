package com.foodsaver.service;

import com.foodsaver.model.Donor;
import com.foodsaver.repository.DonorRepository;
import org.springframework.beans.factory.annotation.Autowired;
import org.springframework.stereotype.Service;

@Service
public class DonorService {

    @Autowired
    private DonorRepository donorRepo;

    public Donor getProfile(String donorId) {
        return donorRepo
            .findById(donorId)
            .orElseThrow(() -> new RuntimeException("Donor not found"));
    }

    public Donor updateProfile(String donorId, Donor updated) {
        Donor donor = getProfile(donorId);
        if (updated.getName() != null) donor.setName(updated.getName());
        if (updated.getContactNumber() != null) donor.setContactNumber(
            updated.getContactNumber()
        );
        if (updated.getAddress() != null) donor.setAddress(
            updated.getAddress()
        );
        return donorRepo.save(donor);
    }
}
