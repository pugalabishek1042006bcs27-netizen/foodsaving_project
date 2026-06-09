package com.foodsaver.repository;

import com.foodsaver.model.ContactMessage;
import org.springframework.data.mongodb.repository.MongoRepository;

public interface ContactMessageRepository
    extends MongoRepository<ContactMessage, String> {}
