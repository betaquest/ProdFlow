<?php

namespace App\Traits;

/**
 * Trait para agregar scopes comunes de filtrado
 * Uso: use HasCommonScopes en tus modelos
 */
trait HasCommonScopes
{
    /**
     * Filtrar solo registros activos
     */
    public function scopeActive($query)
    {
        return $query->where($this->getTable() . '.activo', true);
    }

    /**
     * Filtrar solo registros inactivos
     */
    public function scopeInactive($query)
    {
        return $query->where($this->getTable() . '.activo', false);
    }

    /**
     * Filtrar por ID de proyecto
     */
    public function scopeByProject($query, $projectId)
    {
        return $query->where('proyecto_id', $projectId);
    }

    /**
     * Filtrar por estado
     */
    public function scopeByStatus($query, $status)
    {
        return $query->where('estado', $status);
    }

    /**
     * Filtrar por múltiples estados
     */
    public function scopeByStatuses($query, array $statuses)
    {
        return $query->whereIn('estado', $statuses);
    }

    /**
     * Ordenar por creación descendente (más recientes primero)
     */
    public function scopeLatest($query)
    {
        return $query->orderBy($this->getCreatedAtColumn(), 'desc');
    }

    /**
     * Ordenar por actualización descendente
     */
    public function scopeLatestUpdated($query)
    {
        return $query->orderBy($this->getUpdatedAtColumn(), 'desc');
    }

    /**
     * Paginar con tamaño personalizado
     */
    public function scopePaginate($query, $perPage = 50)
    {
        return $query->paginate($perPage);
    }

    /**
     * Optimizar query con eager loading automático
     */
    public function scopeOptimized($query)
    {
        // Se puede sobrescribir en cada modelo para sus relaciones específicas
        return $query;
    }
}
