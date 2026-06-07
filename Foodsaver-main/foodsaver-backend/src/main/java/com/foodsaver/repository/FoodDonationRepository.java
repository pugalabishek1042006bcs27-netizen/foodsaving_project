package com.foodsaver.repository;

import com.foodsaver.model.FoodDonation;
import java.util.List;
import org.springframework.data.mongodb.repository.MongoRepository;

public interface FoodDonationRepository
    extends MongoRepository<FoodDonation, String>
{
    List<FoodDonation> findByDonorId(String donorId);
    List<FoodDonation> findByStatus(String status);
    List<FoodDonation> findByVolunteerId(String volunteerId);
}
