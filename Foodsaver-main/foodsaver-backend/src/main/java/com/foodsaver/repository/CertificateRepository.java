package com.foodsaver.repository;

import com.foodsaver.model.Certificate;
import java.util.List;
import org.springframework.data.mongodb.repository.MongoRepository;

public interface CertificateRepository
    extends MongoRepository<Certificate, String>
{
    List<Certificate> findByReceiverId(String receiverId);
    List<Certificate> findByStatus(String status);
}
