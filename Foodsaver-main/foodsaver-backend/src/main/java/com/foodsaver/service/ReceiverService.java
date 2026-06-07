package com.foodsaver.service;

import com.foodsaver.model.Receiver;
import com.foodsaver.repository.ReceiverRepository;
import org.springframework.beans.factory.annotation.Autowired;
import org.springframework.stereotype.Service;

@Service
public class ReceiverService {

    @Autowired
    private ReceiverRepository receiverRepo;

    public Receiver getProfile(String receiverId) {
        return receiverRepo
            .findById(receiverId)
            .orElseThrow(() -> new RuntimeException("Receiver not found"));
    }

    public Receiver updateProfile(String receiverId, Receiver updated) {
        Receiver r = getProfile(receiverId);
        if (updated.getReceiverName() != null) r.setReceiverName(
            updated.getReceiverName()
        );
        if (updated.getPhone() != null) r.setPhone(updated.getPhone());
        if (updated.getAddress() != null) r.setAddress(updated.getAddress());
        if (updated.getCity() != null) r.setCity(updated.getCity());
        if (updated.getState() != null) r.setState(updated.getState());
        return receiverRepo.save(r);
    }
}
