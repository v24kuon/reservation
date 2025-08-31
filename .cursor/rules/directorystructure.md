## Directory Structure Guidelines (Laravel 12 + Livewire 3)

### 基本方針
- Laravel 12の標準構成を厳守し、独自のベースフォルダは作らない（必要なものは`app/`配下で役割ごとに整理）。
- ミドルウェア・例外・ルーティング登録は`bootstrap/app.php`、サービスプロバイダは`bootstrap/providers.php`で管理。
- `app/Console/Kernel.php`は不要。コマンドは`app/Console/Commands`に置けば自動登録。
- 新規のミドルウェアファイルは作成しない（`app/Http/Middleware/`は使用しない）。
- 認証・認可はFormRequest + Gates/Policiesを基本とする。
- 外部API（Stripe等）は`app/Services/...`へ集約し、重い処理は`app/Jobs`で非同期化。

### 推奨ディレクトリ（プロジェクト適用例）
```text
app/
  Http/
    Controllers/
      Admin/
      Instructor/
      Web/
    Requests/                # FormRequest（バリデーション）
  Livewire/
    Reservations/
    Favorites/
    Admin/
  Models/
    Store.php
    LessonCategory.php
    SubscriptionPlan.php
    UserSubscription.php
    Lesson.php
    LessonSchedule.php
    Reservation.php
    UserFavorite.php
  Policies/
  Services/
    Stripe/
      CheckoutService.php
      WebhookHandler.php
  Jobs/
  Notifications/
  Events/
bootstrap/
  app.php                    # ミドルウェア・例外・ルーティング登録
  providers.php              # アプリ固有のプロバイダ
config/
database/
  factories/
  migrations/
  seeders/
    BaseCategoriesSeeder.php
    PlansSeeder.php
docs/
  project-overview.md
public/
resources/
  views/
    reservations/
    stores/
    instructors/
    mypage/
    livewire/               # Livewire用のBlade
routes/
  web.php
  api.php
  console.php
tests/
  Feature/
  Unit/
```

### Livewire 3 の配置
- コンポーネントクラス: `app/Livewire/...`
- 対応ビュー: `resources/views/livewire/...`
- 機能単位（例: 予約・お気に入り・管理）でサブフォルダを分け、可読性と保守性を高める。

### 認証・認可
- フォームバリデーションは`app/Http/Requests`のFormRequestで一元化。
- 認可は`app/Policies`でモデル単位に実装し、コントローラ/Livewireから`authorize`/`can`を使用。

### Stripe/Cashier（外部連携）
- Cashier利用を前提に、決済ロジックは`app/Services/Stripe`へ集約。
- Webhook受信→`WebhookHandler`→永続化/状態更新。
- 時間のかかる処理は`app/Jobs`でキュー化。

### 通知
- メール等の通知は`app/Notifications`に配置。
- 予約確定/リマインダー/キャンセル/サブスク更新通知を用途別に分離。

### ルーティング
- 一般Web: `routes/web.php`
- API: `routes/api.php`
- コンソール: `routes/console.php`
- ルート保護はミドルウェアの登録（`bootstrap/app.php`）とPolicyで二重化。

### 補足
- 既存構成に合わせて段階的にディレクトリを拡張する。不要な層の新設は避ける。
- 本ガイドはプロジェクトの要件（予約・サブスク・通知）を踏まえた参考例。実際の作成はPR単位で合意のうえ実施。
