<?php

namespace App\Domains\Admin\Data;

enum AppProfileEnum: string {
  case Renting = 'renting';
  case Loan = 'loan';
  case Store = 'store';
  case School= 'school';
  case Freelance= 'freelance';
}
