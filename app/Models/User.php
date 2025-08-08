<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Auth\Passwords\CanResetPassword;
use Illuminate\Auth\Notifications\ResetPassword;




class User extends Authenticatable implements MustVerifyEmail
{
    use HasFactory, Notifiable, HasApiTokens, CanResetPassword;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'profile_picture',
        'role',
        'gender',         // Added gender
        'dob',            // Added date of birth
        'bio',            // Optional: short biography
        'phone',          // Optional: phone number
        'address',        // Optional: address
        'google_id',      // Optional: Google ID for OAuth
        'google_token',   // Optional: Google token for OAuth
        'google_refresh_token', // Optional: Google refresh token for OAuth
        'status',
        'password_reset_tokens', // Optional: for password reset functionality
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
        'google_token',          // Optional: Google token
        'google_refresh_token',  // Optional: Google refresh token
        'google_id',            // Optional: Google ID
        'password_reset_tokens', // Optional: Password reset tokens
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'dob' => 'date', // Cast dob to date
        ];
    }

    /**
     * The channels the user receives notification broadcasts on.
     *
     * @return string
     */
    public function receivesBroadcastNotificationsOn(): string
    {
        return 'user.' . $this->id;
    }

    public function sendPasswordResetNotification($token)
    {
        $resetUrl = env("FRONTEND_URL") .
            '/reset-password?token=' . $token . '&email=' . urlencode($this->email);

        $this->notify(new class ($token, $resetUrl) extends ResetPassword {
            private $customUrl;
            public function __construct($token, $customUrl)
            {
                parent::__construct($token);
                $this->customUrl = $customUrl;
            }
            protected function resetUrl($notifiable)
            {
                return $this->customUrl;
            }
            protected function buildMailMessage($url)
            {
                return parent::buildMailMessage($url)
                    ->greeting('Reset Your Password'); // Custom greeting
            }
        });
    }
}
