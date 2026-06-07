package com.foodsaver.controller;

import com.foodsaver.dto.ApiResponse;
import com.foodsaver.model.ContactMessage;
import com.foodsaver.repository.ContactMessageRepository;
import java.util.Map;
import org.springframework.beans.factory.annotation.Autowired;
import org.springframework.http.ResponseEntity;
import org.springframework.web.bind.annotation.*;

@RestController
@RequestMapping("/api/contact")
public class ContactController {

    @Autowired
    private ContactMessageRepository contactRepo;

    @PostMapping("/submit")
    public ResponseEntity<?> submit(@RequestBody Map<String, String> body) {
        ContactMessage msg = ContactMessage.builder()
            .name(body.get("name"))
            .email(body.get("email"))
            .message(body.get("message"))
            .build();
        contactRepo.save(msg);
        return ResponseEntity.ok(
            new ApiResponse("success", "Message received. Thank you!")
        );
    }
}
