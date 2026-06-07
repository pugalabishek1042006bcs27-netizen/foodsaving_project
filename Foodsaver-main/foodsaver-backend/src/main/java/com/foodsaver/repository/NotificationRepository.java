package com.foodsaver.repository;

import com.foodsaver.model.Notification;
import java.util.List;
import org.springframework.data.mongodb.repository.MongoRepository;

public interface NotificationRepository
    extends MongoRepository<Notification, String>
{
    List<Notification> findByUserTypeAndUserId(String userType, String userId);
    List<Notification> findByUserTypeAndUserIdOrderByCreatedAtDesc(
        String userType,
        String userId
    );
}
