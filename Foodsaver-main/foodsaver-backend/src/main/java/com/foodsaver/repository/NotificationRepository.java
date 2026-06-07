package com.foodsaver.repository;

import com.foodsaver.model.Notification;
import java.util.List;
import org.springframework.data.jpa.repository.JpaRepository;

public interface NotificationRepository
    extends JpaRepository<Notification, Long>
{
    List<Notification> findByUserTypeAndUserId(String userType, Long userId);
    List<Notification> findByUserTypeAndUserIdOrderByCreatedAtDesc(
        String userType,
        Long userId
    );
}
