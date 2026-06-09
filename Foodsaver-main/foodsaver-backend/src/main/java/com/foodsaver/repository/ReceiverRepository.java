package com.foodsaver.repository;

import com.foodsaver.model.Receiver;
import java.util.Optional;
import org.springframework.data.mongodb.repository.MongoRepository;

public interface ReceiverRepository extends MongoRepository<Receiver, String> {
    Optional<Receiver> findByEmail(String email);
    boolean existsByEmail(String email);
}
