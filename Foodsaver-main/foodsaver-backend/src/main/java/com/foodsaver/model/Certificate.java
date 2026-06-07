package com.foodsaver.model;

import java.time.LocalDateTime;
import lombok.*;
import org.springframework.data.annotation.Id;
import org.springframework.data.mongodb.core.mapping.Document;

@Document(collection = "certificates")
@Data
@NoArgsConstructor
@AllArgsConstructor
@Builder
public class Certificate {

    @Id
    private String certId;

    private String receiverId;
    private String filePath;
    private String status = "Pending";
    private LocalDateTime uploadedAt = LocalDateTime.now();
}
