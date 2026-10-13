# راهنمای توسعه‌دهنده و ایجنت — Arash Settings Framework (ASF)

این سند برای **برنامه‌نویس‌های وردپرس** و **ایجنت‌های هوش مصنوعی** نوشته شده تا بتوانند بدون حدس‌زدن، تنظیمات قالب یا افزونه را با ASF بسازند.

اگر ایجنت هستید: ابتدا بخش «قوانین اجباری» را بخوانید، سپس از اسکلت کپی‌پیست استفاده کنید.

---

## ۱) این فریم‌ورک چیست؟

ASF یک فریم‌ورک تنظیمات ادمین وردپرس است که:

- صفحه تنظیمات (منو / زیرمنو) می‌سازد
- تب، فیلد، شرط نمایش، ریپیتر، رسانه، رنگ، ادیتور دارد
- ذخیره را امن و AJAX انجام می‌دهد (با overlay شیشه‌ای و لودر)
- مقدار ذخیره‌شده را با `asf_get_option()` می‌خواند
- داخل چند افزونه/قالب قابل باندل است (بدون Composer در runtime)

**مسئولیت ASF:** UI تنظیمات + sanitize/validate + persistence  
**مسئولیت شما:** منطق کسب‌وکار افزونه/قالب (نه داخل core فریم‌ورک)

---

## ۲) پیش‌نیازها

| مورد | حداقل |
|------|--------|
| PHP | 7.4+ |
| WordPress | 5.9+ |
| Composer در runtime | لازم نیست |

شناسه‌ها:

| مورد | مقدار |
|------|--------|
| Bootstrap | `arash-settings-framework.php` |
| Namespace | `Arash\SettingsFramework` |
| API اصلی | `Arash\SettingsFramework\Settings` |
| خواندن مقدار | `asf_get_option()`, `asf_get_options()` |
| Prefix توابع | `asf_` |
| Prefix دارایی | `asf-` |
| Text domain | `arash-settings-framework` |

---

## ۳) قوانین اجباری (برای انسان و ایجنت)

1. **فریم‌ورک را فقط یک‌بار load کنید** از مسیر باندل‌شده داخل همان افزونه/قالب.
2. **هرگز** تنظیمات دموی فریم‌ورک را در production روشن نکنید؛ خودتان page/tab ثبت کنید.
3. شناسهٔ `page` / `tab` / `field` باید با این الگو باشد:  
   `/^[a-z][a-z0-9_]{0,63}$/`  
   مثال درست: `my_plugin_settings` — مثال غلط: `My-Plugin` یا `۱تنظیمات`
4. شناسه‌ها باید **یکتا** باشند. ثبت تکراری overwrite نمی‌شود؛ خطا می‌دهد.
5. منطق کسب‌وکار را داخل فایل‌های `src/` فریم‌ورک ننویسید؛ فقط مصرف‌کننده باشید.
6. برای خواندن مقدار از `asf_get_option()` استفاده کنید، نه `get_option()` خام (مگر migration).
7. مقدار پیش‌فرض را در کانفیگ فیلد بگذارید؛ ASF آن را هنگام **خواندن** merge می‌کند، نه هنگام ثبت.
8. HTML خام فقط با `'trusted' => true` یا callback صریح.
9. تب سفارشی با فایل: فقط `template` + `template_base` (allowlist مسیر). مسیر آزاد کاربر ممنوع.
10. اگر چند کپی از ASF لود شود: **اولین کپی فعال می‌ماند** (first loaded wins).

---

## ۴) نصب داخل افزونه

ساختار پیشنهادی:

```text
my-plugin/
├── my-plugin.php
├── includes/
│   ├── arash-settings-framework/     ← کل ریپوی ASF
│   └── settings.php                  ← ثبت صفحه/تب/فیلد شما
└── ...
```

### `my-plugin.php`

```php
<?php
/**
 * Plugin Name: My Plugin
 */

defined( 'ABSPATH' ) || exit;

define( 'MY_PLUGIN_FILE', __FILE__ );
define( 'MY_PLUGIN_DIR', plugin_dir_path( __FILE__ ) );

$asf = MY_PLUGIN_DIR . 'includes/arash-settings-framework/arash-settings-framework.php';
if ( is_readable( $asf ) ) {
	require_once $asf;
}

add_action( 'plugins_loaded', static function () {
	if ( ! class_exists( \Arash\SettingsFramework\Settings::class ) ) {
		return;
	}
	require_once MY_PLUGIN_DIR . 'includes/settings.php';
}, 20 );
```

### `includes/settings.php`

```php
<?php
defined( 'ABSPATH' ) || exit;

use Arash\SettingsFramework\Settings;

Settings::register_page([
	'id'          => 'my_plugin_settings',
	'title'       => __( 'My Plugin', 'my-plugin' ),
	'menu_title'  => __( 'My Plugin', 'my-plugin' ),
	'menu_type'   => 'menu', // یا submenu
	'capability'  => 'manage_options',
	'icon'        => 'dashicons-admin-generic',
	'position'    => 58,
	'description' => __( 'Configure My Plugin.', 'my-plugin' ),
	// 'option_key' => 'my_plugin_settings', // اختیاری؛ پیش‌فرض = id
]);

Settings::register_tab( 'my_plugin_settings', [
	'id'          => 'general',
	'title'       => __( 'General', 'my-plugin' ),
	'description' => __( 'Core options', 'my-plugin' ),
	'icon'        => 'dashicons-admin-home',
	'fields'      => [
		[
			'id'      => 'enable_feature',
			'type'    => 'switch',
			'title'   => __( 'Enable Feature', 'my-plugin' ),
			'default' => false,
		],
		[
			'id'          => 'api_url',
			'type'        => 'url',
			'title'       => __( 'API URL', 'my-plugin' ),
			'placeholder' => 'https://api.example.com',
			'default'     => '',
			'require'     => [
				[ 'enable_feature', '==', true ],
			],
		],
	],
]);
```

---

## ۵) نصب داخل قالب (Theme)

```text
my-theme/
├── functions.php
├── inc/
│   ├── arash-settings-framework/
│   └── theme-settings.php
```

```php
<?php
// functions.php
$asf = get_template_directory() . '/inc/arash-settings-framework/arash-settings-framework.php';
if ( is_readable( $asf ) ) {
	require_once $asf;
}

add_action( 'after_setup_theme', static function () {
	if ( ! class_exists( \Arash\SettingsFramework\Settings::class ) ) {
		return;
	}
	require_once get_template_directory() . '/inc/theme-settings.php';
}, 20 );
```

برای قالب معمولاً submenu زیر Appearance بهتر است:

```php
Settings::register_page([
	'id'          => 'my_theme_settings',
	'title'       => __( 'Theme Settings', 'my-theme' ),
	'menu_title'  => __( 'Theme Settings', 'my-theme' ),
	'menu_type'   => 'submenu',
	'parent_slug' => 'themes.php',
	'capability'  => 'edit_theme_options',
]);
```

---

## ۶) قرارداد API صفحه (`register_page`)

| کلید | نوع | اجباری | توضیح |
|------|-----|--------|--------|
| `id` | string | بله | شناسه پایدار صفحه و پیش‌فرض option key |
| `title` | string | خیر | عنوان صفحه |
| `menu_title` | string | خیر | عنوان منو |
| `menu_type` | `menu`\|`submenu` | خیر | پیش‌فرض `menu` |
| `parent_slug` | string | برای submenu | مثل `options-general.php` |
| `capability` | string | خیر | پیش‌فرض `manage_options` |
| `menu_slug` | string | خیر | پیش‌فرض = `id` |
| `icon` | string | خیر | dashicon برای top-level |
| `position` | int\|null | خیر | ترتیب منو |
| `logo` | string | خیر | URL لوگو هدر |
| `description` | string | خیر | زیرعنوان هدر |
| `option_key` | string | خیر | کلید `get_option` اگر با `id` فرق دارد |

Aliasهای پشتیبانی‌شده: `page_title`, `page_slug`, `page_capability`, `icon_url`, `logo_url`, `page_parent`.

---

## ۷) قرارداد API تب (`register_tab`)

| کلید | نوع | توضیح |
|------|-----|--------|
| `id` | string | شناسه تب |
| `title` | string | عنوان |
| `description` / `desc` | string | توضیح کوتاه سایدبار |
| `icon` | string | کلاس dashicons |
| `fields` | array | لیست فیلدها |
| `callback` | callable | تب کاملاً سفارشی (بدون فرم ذخیره ASF) |
| `template` + `template_base` | string | قالب PHP امن داخل base |

---

## ۸) قرارداد فیلد (schema مشترک)

```php
[
	'id'          => 'field_id',      // الزامی، الگوی a-z...
	'type'        => 'text',          // الزامی
	'title'       => 'Label',
	'description' => 'Help text',
	'default'     => null,
	'required'    => false,
	'disabled'    => false,
	'readonly'    => false,
	'placeholder' => '',
	'options'     => [],              // select/radio/buttonset/multiselect
	'attributes'  => [],              // HTML attrs اضافه
	'class'       => '',
	'require'     => [],              // شرط نمایش
	'sanitize'    => null,            // callable سفارشی
	'validate'    => null,            // callable؛ false یا string خطا
	'renderer'    => null,            // callable رندر سفارشی
]
```

### انواع فیلد پشتیبانی‌شده

| type | مقدار ذخیره‌شده | نکته |
|------|------------------|------|
| `text` | string | |
| `email` | string | نامعتبر → `''` |
| `url` | string | نامعتبر → `''` |
| `password` | string | در لاگ ننویسید |
| `number` | int\|float\|null | `min`/`max`/`step` |
| `textarea` | string | `rows` |
| `hidden` | string | فقط در صورت نیاز |
| `checkbox` | bool | |
| `switch` / `switcher` | bool | |
| `select` / `radio` / `buttonset` | string | allowlist روی `options` |
| `multiselect` | string[] | |
| `color` | `#rrggbb` یا `''` | |
| `image` / `media` | int (attachment ID) | URL با `asf_attachment_url()` |
| `gallery` | int[] | |
| `editor` / `tinymce` / `richtext` | HTML (kses) | داخل repeater توصیه نمی‌شود |
| `repeater` | array of rows | زیرفیلد در `fields` |
| `heading` / `notice` / `html` | ذخیره نمی‌شود | `html` نیاز به `trusted` |
| `callback` | وابسته به sanitize | |

جزئیات بیشتر: [field-types.md](field-types.md)

---

## ۹) خواندن مقادیر (مهم‌ترین بخش مصرف)

```php
// یک فیلد
$enabled = asf_get_option( 'my_plugin_settings', 'enable_feature', false );

// همه فیلدها (با defaults مر‌ج‌شده)
$all = asf_get_options( 'my_plugin_settings' );

// تصویر
$logo_id  = (int) asf_get_option( 'my_plugin_settings', 'logo', 0 );
$logo_url = asf_attachment_url( $logo_id, 'full' );

// ریپیتر
$rows = asf_get_option( 'my_plugin_settings', 'social_networks', [] );
if ( is_array( $rows ) ) {
	foreach ( $rows as $row ) {
		$name = isset( $row['network_name'] ) ? (string) $row['network_name'] : '';
		$url  = isset( $row['network_url'] ) ? (string) $row['network_url'] : '';
		// ...
	}
}
```

### رفتار مقادیر

| وضعیت | نتیجه |
|--------|--------|
| کلید در DB هست | همان مقدار (حتی `false` یا `"0"`) |
| کلید نیست + `$default` پاس شده | `$default` |
| کلید نیست + `$default = null` | default ثبت‌شده فیلد، وگرنه `null` |
| فیلد جدید بعداً اضافه شود | مقدار قبلی دست نمی‌خورد؛ default فقط در خواندن |

**هرگز** `empty( $value )` را برای تشخیص «خاموش بودن سوییچ» استفاده نکنید اگر ممکن است `"0"` معنی‌دار باشد. برای bool از خود مقدار boolean استفاده کنید.

---

## ۱۰) شرطی‌ها (`require`)

```php
[
	'id'      => 'bucket',
	'type'    => 'text',
	'title'   => 'Bucket',
	'require' => [
		'relation' => 'AND', // یا OR
		[ 'enable_remote_storage', '==', true ],
		[ 'storage_provider', 'in', [ 's3', 'minio' ] ],
	],
]
```

اپراتورها: `==`, `!=`, `empty`, `not_empty`, `in`, `not_in`

نکته امنیتی: مخفی بودن فیلد در UI، اجازهٔ ذخیرهٔ فیلد ثبت‌نشده نمی‌دهد. فقط فیلدهای registered ذخیره می‌شوند.

جزئیات: [conditional-fields.md](conditional-fields.md)

---

## ۱۱) ریپیتر

```php
[
	'id'       => 'social_networks',
	'type'     => 'repeater',
	'title'    => 'Social Networks',
	'min_rows' => 0,
	'max_rows' => 10,
	'fields'   => [
		[ 'id' => 'network_name', 'type' => 'text', 'title' => 'Name' ],
		[ 'id' => 'network_url',  'type' => 'url',  'title' => 'URL' ],
		[ 'id' => 'network_icon', 'type' => 'image','title' => 'Icon' ],
	],
]
```

ساختار ذخیره‌شده:

```php
[
	[ 'network_name' => 'Telegram', 'network_url' => 'https://t.me/x', 'network_icon' => 12 ],
	[ 'network_name' => 'X', 'network_url' => 'https://x.com/y', 'network_icon' => 0 ],
]
```

---

## ۱۲) تب سفارشی (Dashboard / گزارش)

با callback:

```php
Settings::register_tab( 'my_plugin_settings', [
	'id'       => 'dashboard',
	'title'    => 'Dashboard',
	'icon'     => 'dashicons-dashboard',
	'callback' => static function ( string $page_id, array $tab, array $values ): void {
		$on = ! empty( $values['enable_feature'] );
		echo '<p>' . esc_html( $on ? 'Feature ON' : 'Feature OFF' ) . '</p>';
	},
]);
```

با فایل امن:

```php
Settings::register_tab( 'my_plugin_settings', [
	'id'            => 'reports',
	'title'         => 'Reports',
	'template'      => 'reports.php', // نسبی نسبت به base
	'template_base' => MY_PLUGIN_DIR . 'includes/views',
]);
```

---

## ۱۳) ذخیره / ریست (رفتار runtime)

- ذخیره و ریست از UI با **AJAX** (`admin-ajax.php` → `asf_save_settings`) انجام می‌شود.
- هنگام ذخیره: کل باکس تنظیمات overlay شیشه‌ای + لودر + غیرفعال می‌شود.
- بدون JS: POST کلاسیک همچنان کار می‌کند (fallback).
- شما معمولاً نیازی به نوشتن کد ذخیره ندارید؛ ASF انجام می‌دهد.
- برای مشاهدهٔ موفقیت ذخیره: هوک `asf_saved`.

```php
add_action( 'asf_saved', static function ( array $values, string $page_id ) {
	if ( 'my_plugin_settings' !== $page_id ) {
		return;
	}
	// مثلاً پاک کردن cache
}, 10, 2 );
```

---

## ۱۴) گسترش (بدون دست زدن به core)

### فیلد سفارشی

```php
use Arash\SettingsFramework\Fields\AbstractField;
use Arash\SettingsFramework\Settings;

final class MySlugField extends AbstractField {
	public static function type(): string { return 'slug'; }

	public function sanitize( $value, array $field ) {
		return sanitize_title( (string) $value );
	}

	public function render( array $field, $value, array $context ): void {
		printf(
			'<input type="text" class="asf-input" id="%1$s" name="%2$s" value="%3$s" />',
			esc_attr( $context['id'] ),
			esc_attr( $context['name'] ),
			esc_attr( (string) $value )
		);
	}
}

Settings::register_field_type( 'slug', MySlugField::class );
```

### هوک‌های پرکاربرد

| هوک | کاربرد |
|-----|--------|
| `asf_booted` | بعد از boot |
| `asf_normalize_field` | تغییر کانفیگ فیلد |
| `asf_sanitize_field` / `asf_sanitize_page` | sanitize |
| `asf_validate_page` | خطاهای اعتبارسنجی |
| `asf_pre_save_options` | قبل از `update_option` |
| `asf_saved` / `asf_reset` | بعد از موفقیت |
| `asf_render_field` | تغییر HTML رندر |
| `asf_get_option` | فیلتر مقدار خوانده‌شده |

لیست کامل: [extensibility.md](extensibility.md)

---

## ۱۵) امنیت — چک‌لیست مصرف‌کننده

- capability مناسب صفحه را ست کنید (`manage_options` یا `edit_theme_options`).
- به فیلد `html` بدون `trusted` اعتماد نکنید.
- callback/template فقط کد خودتان باشد.
- خروجی فرانت را همیشه escape کنید حتی اگر از ASF آمده:

```php
echo esc_html( (string) asf_get_option( 'my_plugin_settings', 'site_name', '' ) );
echo esc_url( (string) asf_get_option( 'my_plugin_settings', 'api_url', '' ) );
```

جزئیات: [security.md](security.md)

---

## ۱۶) الگوی کامل آمادهٔ کپی (افزونه)

```php
<?php
defined( 'ABSPATH' ) || exit;

use Arash\SettingsFramework\Settings;

const MY_OPT_PAGE = 'acme_settings';

Settings::register_page([
	'id'         => MY_OPT_PAGE,
	'title'      => 'Acme',
	'menu_title' => 'Acme',
	'menu_type'  => 'submenu',
	'parent_slug'=> 'options-general.php',
	'capability' => 'manage_options',
]);

Settings::register_tab( MY_OPT_PAGE, [
	'id'     => 'general',
	'title'  => 'General',
	'icon'   => 'dashicons-admin-generic',
	'fields' => [
		[
			'id'      => 'is_enabled',
			'type'    => 'switch',
			'title'   => 'Enable',
			'default' => true,
		],
		[
			'id'      => 'mode',
			'type'    => 'buttonset',
			'title'   => 'Mode',
			'options' => [
				'live'    => 'Live',
				'sandbox' => 'Sandbox',
			],
			'default' => 'sandbox',
		],
		[
			'id'      => 'primary_color',
			'type'    => 'color',
			'title'   => 'Brand color',
			'default' => '#0f766e',
		],
	],
]);

// مصرف در منطق افزونه:
add_action( 'init', static function () {
	if ( ! asf_get_option( MY_OPT_PAGE, 'is_enabled', true ) ) {
		return;
	}
	$mode = asf_get_option( MY_OPT_PAGE, 'mode', 'sandbox' );
	// ...
} );
```

نمونهٔ غنی‌تر: `examples/asf-example-plugin/asf-example-plugin.php`

---

## ۱۷) دستورالعمل مخصوص ایجنت‌ها (Agent Playbook)

وقتی کاربر می‌گوید «تنظیمات بساز» یا «settings page اضافه کن»:

### گام‌ها

1. بررسی کن ASF داخل پروژه هست یا باید باندل شود (`arash-settings-framework.php`).
2. یک فایل تنظیمات جدا بساز (مثلاً `includes/settings.php`)؛ core فریم‌ورک را ویرایش نکن.
3. `register_page` با `id` یکتا و پایدار.
4. تب‌ها را بر اساس دامنهٔ محصول جدا کن (General / Appearance / Integrations …).
5. برای on/off از `switch` استفاده کن، نه select بله/خیر.
6. فیلدهای وابسته را با `require` بساز.
7. لیست‌های تکراری را با `repeater` بساز.
8. خواندن مقدار را فقط با `asf_get_option` انجام بده.
9. تست ذهنی: duplicate id؟ capability؟ escape در فرانت؟

### ممنوع برای ایجنت

- کپی‌کردن کلاس‌های Optionino یا فریم‌ورک دیگر داخل ASF
- نوشتن SQL مستقیم برای تنظیمات
- ذخیرهٔ secret در `default` که در UI همیشه دیده شود بدون نیاز
- اضافه کردن React/Vue برای صفحهٔ تنظیمات ASF
- لود کردن Tailwind CDN یا فونت ریموت برای UI ASF (فونت Peyda لوکال است)

### خروجی مورد انتظار ایجنت

- فایل ثبت تنظیمات
- (در صورت نیاز) helperهای خواندن مقدار در منطق افزونه/قالب
- بدون تغییر غیرضروری در `src/` فریم‌ورک

---

## ۱۸) اشکال‌زدایی سریع

| مشکل | علت محتمل | کار |
|------|-----------|-----|
| منو نمی‌آید | class لود نشده / capability | `class_exists(Settings::class)` و نقش کاربر |
| فیلد ذخیره نمی‌شود | id نامعتبر / type ناشناخته | الگوی id و type را چک کن |
| default DB را پر کرده | اشتباه مفهومی شما | ASF default را روی register نمی‌نویسد |
| مقدار `false` برنمی‌گردد | استفاده از `empty()` | مستقیم با `=== false` یا cast bool |
| conflict نسخه | دو کپی major مختلف | `asf_loader_info()` و WP_DEBUG |
| Add row کار نمی‌کند | JS لود نشده | فقط روی صفحه ASF enqueue می‌شود |

تشخیص لودر:

```php
$info = asf_loader_info();
// active / skipped / conflicts
```

---

## ۱۹) لینک اسناد مرتبط

- [getting-started.md](getting-started.md) — شروع سریع
- [architecture.md](architecture.md) — معماری و lifecycle
- [field-types.md](field-types.md) — همه فیلدها
- [conditional-fields.md](conditional-fields.md) — شرطی‌ها
- [security.md](security.md) — امنیت
- [extensibility.md](extensibility.md) — هوک‌ها و فیلد سفارشی

---

## ۲۰) نسخه و سازگاری

- نسخهٔ فعلی فریم‌ورک: ثابت `ASF_VERSION`
- سیاست چندنسخه: first loaded wins
- API عمومی پایدار در major فعلی؛ breaking change فقط با major جدید

اگر برای محصول تجاری باندل می‌کنید، کل پوشهٔ ASF را داخل افزونه کپی کنید و از مسیر نسبی `require_once` کنید.
