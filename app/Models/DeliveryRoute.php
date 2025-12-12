<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

class DeliveryRoute extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'driver',
        'assistant',
        'planned_start',
        'actual_start',
    ];

    protected $casts = [
        'planned_start' => 'datetime',
        'actual_start' => 'datetime',
    ];

    public function orders()
    {
        return $this->hasMany(CustomerOrder::class);
    }

    public function stops()
    {
        return $this->hasMany(RouteStop::class);
    }

    public function pendingOrders()
    {
        return $this->orders()->whereIn('status', ['pending', 'loaded'])->get();
    }

    public function completedOrders()
    {
        return $this->orders()->whereIn('status', ['delivered', 'completed'])->get();
    }

    public function pendingDeliveriesCount(): int
    {
        return $this->orders()->whereIn('status', ['pending', 'loaded'])->count();
    }

    public function getTimeDifference(): ?int
    {
        if ($this->planned_start && $this->actual_start) {
            $planned = Carbon::parse($this->planned_start);
            $actual = Carbon::parse($this->actual_start);
            // Return positive if late (actual > planned), negative if early (actual < planned)
            // diffInMinutes with false returns signed difference: actual - planned
            return $actual->diffInMinutes($planned, false);
        }
        return null;
    }
}

