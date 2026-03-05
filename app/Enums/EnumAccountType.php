<?php

namespace App\Enums;

enum EnumAccountType: int
{
    case BaseCard = 1;
    case Card = 2;
    case Teacher = 3;
    case Student = 4;
    case Expense = 5;
    case Box = 6;
    case Salary = 7;
    case Profit = 8;
}
