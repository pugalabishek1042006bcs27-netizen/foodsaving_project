package com.foodsaver.model;

import jakarta.persistence.*;
import java.math.BigDecimal;
import java.time.LocalDateTime;
import lombok.*;

@Entity
@Table(name = "volunteer_locations")
@Data
@NoArgsConstructor
@AllArgsConstructor
@Builder
public class VolunteerLocation {

    @Id
    @GeneratedValue(strategy = GenerationType.IDENTITY)
    private Long id;

    private Long volunteerId;
    private BigDecimal lat;
    private BigDecimal lng;
    private BigDecimal heading;
    private BigDecimal speed;
    private LocalDateTime createdAt = LocalDateTime.now();
}
