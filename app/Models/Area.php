<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Spatie\Permission\Models\Role;

class Area extends Model
{
    protected $fillable = [
        'nombre',
        'descripcion',
        'activo',
    ];

    protected $casts = [
        'activo' => 'boolean',
    ];

    // Relación: Un área tiene muchos usuarios
    public function users(): HasMany
    {
        return $this->hasMany(User::class);
    }

    // Relación: Un área tiene muchos roles
    public function roles(): BelongsToMany
    {
        return $this->belongsToMany(Role::class, 'area_role')
            ->withTimestamps();
    }

    /**
     * Verificar si el área tiene un rol específico
     */
    public function hasRole(string $roleName): bool
    {
        return $this->roles()->where('name', $roleName)->exists();
    }

    /**
     * Verificar si el área tiene un permiso específico (a través de sus roles)
     */
    public function hasPermission(string $permissionName): bool
    {
        return $this->roles()
            ->whereHas('permissions', function ($query) use ($permissionName) {
                $query->where('name', $permissionName);
            })
            ->exists();
    }
}
