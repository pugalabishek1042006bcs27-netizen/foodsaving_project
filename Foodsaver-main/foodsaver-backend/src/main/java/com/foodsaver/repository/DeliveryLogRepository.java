package com.foodsaver.repository;

import com.foodsaver.model.DeliveryLog;
import java.util.List;
import org.springframework.data.jpa.repository.JpaRepository;

public interface DeliveryLogRepository
    extends JpaRepository<DeliveryLog, Long>
{
    List<DeliveryLog> findByVolunteerId(Long volunteerId);
    List<DeliveryLog> findByDonationId(Long donationId);
}
