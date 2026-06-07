package com.foodsaver.repository;

import com.foodsaver.model.FoodDonation;
import java.util.List;
import org.springframework.data.jpa.repository.JpaRepository;

public interface FoodDonationRepository
    extends JpaRepository<FoodDonation, Long>
{
    List<FoodDonation> findByDonorId(Long donorId);
    List<FoodDonation> findByStatus(String status);
    List<FoodDonation> findByVolunteerId(Long volunteerId);
}
