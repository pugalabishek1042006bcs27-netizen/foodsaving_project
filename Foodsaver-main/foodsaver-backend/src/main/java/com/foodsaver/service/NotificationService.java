package com.foodsaver.service;

import com.foodsaver.model.Notification;
import com.foodsaver.repository.NotificationRepository;
import java.util.ArrayList;
import java.util.List;
import org.springframework.beans.factory.annotation.Autowired;
import org.springframework.stereotype.Service;

@Service
public class NotificationService {

    @Autowired
    private NotificationRepository notifRepo;

    public void send(String userType, String userId, String message) {
        Notification n = Notification.builder()
            .userType(userType)
            .userId(userId)
            .message(message)
            .build();
        notifRepo.save(n);
    }

    public void broadcast(String userType, String message) {
        send(userType, "0", message);
    }

    public List<Notification> getNotifications(String userType, String userId) {
        List<Notification> personal = new ArrayList<>(
            notifRepo.findByUserTypeAndUserId(userType, userId)
        );
        List<Notification> broadcast = notifRepo.findByUserTypeAndUserId(
            userType,
            "0"
        );
        personal.addAll(broadcast);
        personal.sort((a, b) -> b.getCreatedAt().compareTo(a.getCreatedAt()));
        return personal;
    }
}
