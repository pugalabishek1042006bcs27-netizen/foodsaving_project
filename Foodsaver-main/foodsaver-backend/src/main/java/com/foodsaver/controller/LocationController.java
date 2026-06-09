package com.foodsaver.controller;

import java.util.Map;
import org.springframework.messaging.handler.annotation.MessageMapping;
import org.springframework.messaging.handler.annotation.SendTo;
import org.springframework.stereotype.Controller;

@Controller
public class LocationController {

    @MessageMapping("/location")
    @SendTo("/topic/location")
    public Map<String, Object> sendLocation(Map<String, Object> location) {
        location.put("timestamp", System.currentTimeMillis());
        return location;
    }
}
