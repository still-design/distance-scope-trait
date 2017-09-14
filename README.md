# Laravel distance scope trait
Distance scope trait to Laravel models

## Installation

```
"stilldesign/distance-scope-trait": "dev-master"
```

## Use

In the model

```
<?php

namespace App\Models;

use Stilldesign\DistanceScopeTrait\DistanceScopeTrait;

class ExampleModel extends Model
{

use DistanceScopeTrait;

...
}
```

In The controller


```
<?php

use App\Models\ExampleModel;

class ExampleController extends Model
{

    public function index
    (
        ...
        $exampleModelItems = ExampleModel::distance($lattitude, $longitue, 100)->get();
        // all items in the given coordinates, within 100 kilometers
        ...
    )

...
}
```
