package com.foodsaver.repository;

import com.foodsaver.model.VolunteerLocation;
import java.util.List;
import org.springframework.data.jpa.repository.JpaRepository;

public interface VolunteerLocationRepository
    extends JpaRepository<VolunteerLocation, Long>
{
    List<VolunteerLocation> findByVolunteerId(Long volunteerId);
}
