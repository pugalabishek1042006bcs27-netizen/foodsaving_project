package com.foodsaver.controller;

import com.foodsaver.dto.ApiResponse;
import com.foodsaver.dto.ContactRequest;
import com.foodsaver.model.ContactMessage;
import com.foodsaver.repository.ContactMessageRepository;
import jakarta.validation.Valid;
import org.springframework.beans.factory.annotation.Autowired;
import org.springframework.http.ResponseEntity;
import org.springframework.web.bind.annotation.*;

@RestController
@RequestMapping("/api/contact")
public class ContactController {

    @Autowired
    private ContactMessageRepository contactRepo;

    @PostMapping("/submit")
    public ResponseEntity<?> submit(@Valid @RequestBody ContactRequest req) {
        ContactMessage msg = ContactMessage.builder()
            .name(req.getName())
            .email(req.getEmail())
            .message(req.getMessage())
            .build();
        contactRepo.save(msg);
        return ResponseEntity.ok(
            new ApiResponse("success", "Message received. Thank you!")
        );
    }
}
