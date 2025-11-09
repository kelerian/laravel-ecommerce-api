<?php

namespace App\Models\Orders;


use App\Events\ChangeOrderStatusEvent;
use App\Models\Products\Product;
use App\Models\Users\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Notifications\Notifiable;
use Illuminate\Notifications\Notification;

class Order extends Model
{

    use Notifiable;
    protected $fillable = [
        'email',
        'phone',
        'address',
        'pay_type_id',
        'order_status_id',
        'final_price',
        'user_id',
        'changes_in_stock'
    ];
    protected $casts = [
        'changes_in_stock' => 'array',
    ];
    protected $table = 'orders';

    public function orderStatus(): BelongsTo
    {
        return $this->belongsTo(OrderStatus::class);
    }

    public function payType(): BelongsTo
    {
        return $this->belongsTo(PayType::class);
    }
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function orderItem():HasMany
    {
        return $this->hasMany(OrderItem::class);
    }

    public function products(): BelongsToMany
    {
        return $this->belongsToMany(
            Product::class,
            'order_items',
            'order_id',
            'product_id'
        )
            ->withPivot(
                'product_name',
                'price',
                'quantity'
            );
    }

    public function changeStatus(string $newStatus):bool
    {

        $currentStatus = $this->orderStatus()->first();

        if ($currentStatus->slug == $newStatus) {
            return false;
        }

        $newStatus = OrderStatus::where('slug', $newStatus)->firstOrFail();

        $this->orderStatus()->associate($newStatus);
        $this->save();

        event(new ChangeOrderStatusEvent($this, $newStatus, $currentStatus));

        return true;
    }

    public function routeNotificationForMail(Notification $notification): array|string
    {
        $email = $this->user()->firstOrFail()->email;

        return $email;

    }

}
