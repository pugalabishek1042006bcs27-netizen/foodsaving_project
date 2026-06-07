package com.foodsaver.repository;

import com.foodsaver.model.FoodRequest;
import java.util.List;
import org.springframework.data.jpa.repository.JpaRepository;

public interface FoodRequestRepository
    extends JpaRepository<FoodRequest, Long>
{
    List<FoodRequest> findByReceiverId(Long receiverId);
    List<FoodRequest> findByDonationId(Long donationId);
    List<FoodRequest> findByVolunteerId(Long volunteerId);
}
