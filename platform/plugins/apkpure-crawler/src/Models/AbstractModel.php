<?php

namespace Wallis\ApkpureCrawler\Models;

use Botble\Base\Models\BaseModel;
use Carbon\CarbonInterface;

/**
 * @property string|int $id
 * @property CarbonInterface $created_at
 * @property CarbonInterface $updated_at
 */
abstract class AbstractModel extends BaseModel
{
}
