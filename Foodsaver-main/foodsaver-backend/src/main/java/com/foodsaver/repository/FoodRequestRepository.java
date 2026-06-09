package com.foodsaver.repository;

import com.foodsaver.model.FoodRequest;
import java.util.List;
import org.springframework.data.mongodb.repository.MongoRepository;

public interface FoodRequestRepository
    extends MongoRepository<FoodRequest, String>
{
    List<FoodRequest> findByReceiverId(String receiverId);
    List<FoodRequest> findByDonationId(String donationId);
    List<FoodRequest> findByVolunteerId(String volunteerId);
}
