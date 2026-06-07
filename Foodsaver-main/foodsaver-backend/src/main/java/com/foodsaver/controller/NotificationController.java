package com.foodsaver.controller;

import com.foodsaver.service.NotificationService;
import org.springframework.beans.factory.annotation.Autowired;
import org.springframework.http.ResponseEntity;
import org.springframework.web.bind.annotation.*;

@RestController
@RequestMapping("/api/notifications")
public class NotificationController {

    @Autowired
    private NotificationService notifService;

    @GetMapping("/{userType}/{userId}")
    public ResponseEntity<?> getNotifications(
        @PathVariable String userType,
        @PathVariable String userId
    ) {
        return ResponseEntity.ok(
            notifService.getNotifications(userType, userId)
        );
    }
}
