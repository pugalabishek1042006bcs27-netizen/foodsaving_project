package com.foodsaver.repository;

import com.foodsaver.model.DeliveryLog;
import java.util.List;
import org.springframework.data.mongodb.repository.MongoRepository;

public interface DeliveryLogRepository
    extends MongoRepository<DeliveryLog, String>
{
    List<DeliveryLog> findByVolunteerId(String volunteerId);
    List<DeliveryLog> findByDonationId(String donationId);
}
