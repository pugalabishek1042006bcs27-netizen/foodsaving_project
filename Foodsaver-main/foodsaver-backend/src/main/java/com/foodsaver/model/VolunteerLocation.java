package com.foodsaver.model;

import org.springframework.data.annotation.Id;
import org.springframework.data.mongodb.core.mapping.Document;
import java.math.BigDecimal;
import java.time.LocalDateTime;
import lombok.*;

@Document(collection = "volunteer_locations")
@Data
@NoArgsConstructor
@AllArgsConstructor
@Builder
public class VolunteerLocation {

    @Id
    private String id;

    private String volunteerId;
    private BigDecimal lat;
    private BigDecimal lng;
    private BigDecimal heading;
    private BigDecimal speed;
    private LocalDateTime createdAt = LocalDateTime.now();
}
