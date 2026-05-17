<?php

namespace CampusTrack\Contract;

/**
 * Position Provider Interface
 * 
 * Interface for indoor positioning system integration.
 * Implement this to plug in real-time positioning services
 * such as Situm, IndoorAtlas, or custom BLE solutions.
 */
interface PositionProviderInterface
{
    /**
     * Get the current position of the user
     * 
     * @return array{x: float, y: float, floor_id: ?int, accuracy: float, timestamp: int}
     */
    public function getCurrentPosition(): array;

    /**
     * Check if the positioning system is available
     */
    public function isAvailable(): bool;

    /**
     * Get the provider name
     */
    public function getProviderName(): string;

    /**
     * Start tracking position updates
     * 
     * @param callable $callback Function to call on position updates
     */
    public function startTracking(callable $callback): void;

    /**
     * Stop tracking position updates
     */
    public function stopTracking(): void;
}
