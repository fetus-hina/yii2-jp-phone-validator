yii2-jp-phone-validator
=======================

相沢陽菜先生の次回作にご期待ください。

動作環境
--------

- PHP 5.4 以上
- Yii framework 2.0

インストール
------------

この通りやってもまだだめです。
現時点ではまだ [yii2-extra-validator](https://github.com/fetus-hina/yii2-extra-validator) を使ってください。

1. [Composer](https://getcomposer.org/) をダウンロードして使用可能な状態にします。
2. 必要であれば Yii Framework2 のプロジェクトを作成します。
3. `php composer.phar require jp3cki/yii2-jp-phone-validator`

使い方
------

### JpPhoneValidator ###

このバリデータは入力が日本の電話番号らしい文字列であることを検証します。

市外局番が存在するかなどのチェックは行えますが、番号が実在することは確認できません。

フリーダイヤル等を許容するかどうか、ハイフンを許容するかどうかを設定できます（ハイフンの位置が間違っている場合はエラーになります）。
`110` 等の特番は扱えません。

Model class example:
```php
namespace app\models;

use yii\base\Model;
use jp3cki\yii2\validators\JpPhoneValidator;

class YourCustomForm extends Model
{
    public $value;

    public function rules()
    {
        return [
            [['value'], JpPhoneValidator::className(),
                'types' => JpPhoneValidator::FLAG_CONSUMERS, // 意味は後述
                'hyphen' => null, // 意味は後述
            ],
        ];
    }
}
```

`types`: 許容する電話番号の種類を設定します。複数の種類を受け入れる場合は bit-or `|` で接続します。デフォルトは `FLAG_CONSUMERS` です。

  * `FLAG_LANDLINE`: 固定電話の番号を受け入れます。
  * `FLAG_MOBILE`: `090` `080` `070` の携帯電話・PHSを受け入れます（番号ポータビリティ等の都合により、電話会社を識別したりPHSを識別したりはできません）。
  * `FLAG_IP_PHONE`: `050` のIP電話を受け入れます（050でないIP電話は固定電話と区別がつきません）。
  * `FLAG_FREE_DIAL`: `0120` のフリーダイヤルを受け入れます。ハイフンの位置は`0120-000-000`か`0120-00-0000`を受け入れるようになっています。 `FLAG_FREE_ACCESS` も参照してください。
  * `FLAG_FREE_ACCESS`: `0800` のフリーアクセスを受け入れます。 `FLAG_FREE_DIAL` も参照してください。
  * `FLAG_NAV_DIAL`: `0570` のナビダイヤルを受け入れます。
  * `FLAG_DIAL_Q2`: `0990` のダイヤルQ2を受け入れます。ダイヤルQ2は既にサービスを終了したため利用することはないでしょう。（このライブラリからもじきに削除されます）
  * `FLAG_PAGER`: `020` のポケットベルを受け入れます。

利便性のために次の定数も準備されています。

  * `FLAG_CONSUMERS`: `FLAG_LANDLINE|FLAG_MOBILE|FLAG_IP_PHONE`。顧客情報を登録してもらう際に一般的に必要となりそうな番号です。
  * `FLAG_ALL`: サポートしている全ての種類を受け入れます。

なお、`FLAG_FREE_DIAL` と `FLAG_FREE_ACCESS` を分けて設定する意味はあまりないものと推測されます。

`hyphen`: ハイフンの許可状況を設定します。

  * `null`: ハイフンの有無を気にしません（ハイフンが記入されている場合は正しい位置にハイフンがある必要があります）。
  * `true`: ハイフンを必須とします。（正しい位置にハイフンがある必要があります）
  * `false`: ハイフンを許容しません。（数字のみの羅列である必要があります）


ライセンス
----------

[The MIT License](https://github.com/fetus-hina/yii2-extra-validator/blob/master/LICENSE).

```
The MIT License (MIT)

Copyright (c) 2015 AIZAWA Hina <hina@bouhime.com>

Permission is hereby granted, free of charge, to any person obtaining a copy
of this software and associated documentation files (the "Software"), to deal
in the Software without restriction, including without limitation the rights
to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
copies of the Software, and to permit persons to whom the Software is
furnished to do so, subject to the following conditions:

The above copyright notice and this permission notice shall be included in all
copies or substantial portions of the Software.

THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN THE
SOFTWARE.
```

貢献
----

パッチ・issue の報告を歓迎します。

- このレポジトリを fork して、issue や feature ごとに別々のブランチを作成した上で作業をおこなってください。（master ブランチ上で作業すると Pull Request を作成・運用する際の妨げになります）
- 必ずテストを作成し、`make test` を実行してください。
- コーディングスタイルは PSR-2 です（メソッド・プロパティ・変数は lowerCamelCase、クラスは UpperCamelCase でお願いします）
    - `make check-style` を行うと簡単な静的解析とコーディングスタイルチェックが行えます。
    - 簡単な違反は `make fix-style` で自動修正できます。
- Pull Request は issue や feature ごとに作成してください。（1feature:1ブランチ:1PRになるはずです）
- Pull Request 作成時に行われる Travis-CI や他のフックによるチェック結果に目を通し、必要であれば修正をおこなってください。
    - 原則としてチェックを通らない状態では取り込みは行いません。
