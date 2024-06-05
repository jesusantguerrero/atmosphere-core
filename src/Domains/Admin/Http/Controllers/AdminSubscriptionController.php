<?php

namespace Freesgen\Atmosphere\Domains\Admin\Http\Controllers;

use Insane\Treasurer\Models\Subscription;
use Freesgen\Atmosphere\Http\InertiaController;

class AdminSubscriptionController extends InertiaController
{
  protected $authorizedUser = false;
  protected $authorizedTeam = false;

  public function __construct(Subscription $subscription)
  {
      $this->model = $subscription;
      $this->searchable = ['name'];
      $this->templates = [
          "index" => 'Admin/Subscriptions/Index',
          "show" => 'Admin/Subscriptions/Show'
      ];
      $this->validationRules = [ ];
      $this->sorts = ['created_at'];
      $this->includes = [];
      $this->filters = [];
      $this->page = 1;
      $this->limit = 10;

      // $this->authorizeResource(Team::class, 'index');
      // $this->authorizeResource(Team::class, 'show');
    }
}
