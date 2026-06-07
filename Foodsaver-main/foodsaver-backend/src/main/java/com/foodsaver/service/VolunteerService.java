package com.foodsaver.service;

import com.foodsaver.model.Volunteer;
import com.foodsaver.repository.VolunteerRepository;
import org.springframework.beans.factory.annotation.Autowired;
import org.springframework.stereotype.Service;

@Service
public class VolunteerService {

    @Autowired
    private VolunteerRepository volunteerRepo;

    public Volunteer getProfile(Long volunteerId) {
        return volunteerRepo
            .findById(volunteerId)
            .orElseThrow(() -> new RuntimeException("Volunteer not found"));
    }

    public Volunteer updateProfile(Long volunteerId, Volunteer updated) {
        Volunteer v = getProfile(volunteerId);
        if (updated.getName() != null) v.setName(updated.getName());
        if (updated.getContactNumber() != null) v.setContactNumber(
            updated.getContactNumber()
        );
        if (updated.getRegion() != null) v.setRegion(updated.getRegion());
        if (updated.getAvailability() != null) v.setAvailability(
            updated.getAvailability()
        );
        return volunteerRepo.save(v);
    }
}
