# ヨガ・ピラティス教室予約システム - プロジェクト概要

## プロジェクト概要

### アプリケーションの目的
ヨガやピラティス等の教室の予約を自動化するシステムです。

### 主要機能
- **月謝制予約システム**: Stripeによるサブスクリプション決済
- **複数店舗対応**: 店舗ごとのレッスン管理
- **レッスン種類**: パーソナル・グループレッスン対応
- **予約管理**: 月謝プランに応じた予約回数制限

## 技術スタック

### バックエンド
- **PHP**: 8.4.11
- **Laravel Framework**: 12.26.3
- **データベース**:
  - 開発環境: SQLite
  - 本番環境: MySQL

### フロントエンド
- **基本**: Laravel Blade（サーバーサイドレンダリング）
- **動的機能**: Livewire（必要な部分のみ）
- **クライアントサイド**: Alpine.js（Livewireに含まれる）

### 決済システム
- **Stripe**: サブスクリプション決済
- **Laravel Cashier**: Stripe統合ライブラリ
- **Stripe Checkout**: 決済処理
- **Webhook**: 自動同期

#### Stripe連携とプラン紐付け（指針）
- プラン定義はアプリ（`subscription_plans`）に保持し、請求は常に`stripe_price_id`を用いる（アプリ側の`price`は表示用）。
- Stripeで作成したProduct/PriceのID（`stripe_product_id`/`stripe_price_id`）を管理画面からプランに登録し、一意制約を付与する。
- **管理画面での自動入力機能**: `stripe_price_id`入力時にStripe APIから価格を自動取得し、`price`フィールドに自動入力する（サーバーサイドAPI経由・認可必須・レート制限適用: Gate `manage-subscription-plans`、throttle設定）。
- 決済はStripe Checkoutを優先（保存済みPM向けの`->create()`ではなく`->checkout([...])`を使用）。
- 契約作成後はWebhookで同期：`checkout.session.completed`/`customer.subscription.created`/`invoice.paid`/`invoice.payment_failed`などを処理。
- 利用回数リセットは`invoice.paid`で当期開始時に`current_month_used_count=0`へ更新。
- 監査性確保のため、`user_subscriptions`に当期の`stripe_price_id`（任意で`monthly_quota`）も保存。
- 管理画面入力バリデーション：`stripe_product_id`は`/^prod_/`、`stripe_price_id`は`/^price_/`にマッチ必須。

### 開発ツール
- **Laravel Head**: 開発環境
- **Laravel Boost**: ^1.0
- **Laravel Pint**: ^1.24
- **Laravel Sail**: ^1.41
- **Laravel Pail**: ^1.2.2
- **PHPUnit**: ^11.5.3

## システム設計

### データベース設計（実装完了）

#### 実装済みテーブル構成
```
users (ユーザー) ✅
├── id, name, email, password, role (user/instructor/admin), email_verified_at, created_at, updated_at
├── リレーション: taughtLessons, subscriptions, reservations, favorites

stores (店舗) ✅
├── id, name, address, phone, access_info, google_map_url, parking_info, notes, is_active, created_at, updated_at
├── リレーション: lessons

lesson_categories (レッスンカテゴリ) ✅
├── id, parent_id, name, description, is_active, sort_order, created_at, updated_at
├── リレーション: parent, children, lessons
├── 階層構造: 親→子→孫（3階層対応）

subscription_plans (月謝プラン) ✅
├── id, name, price, lesson_count, allowed_category_ids (JSON), stripe_product_id, stripe_price_id, description, is_active, created_at, updated_at
├── リレーション: userSubscriptions
├── 制約: stripe_product_id, stripe_price_id に一意制約

user_subscriptions (ユーザーの月謝契約) ✅
├── id, user_id, plan_id, stripe_subscription_id, status, payment_status, failure_reason, current_period_start, current_period_end, current_month_used_count, created_at, updated_at
├── リレーション: user, plan, reservations

lessons (レッスン) ✅
├── id, store_id, name, category_id, instructor_user_id, duration, capacity, booking_deadline_hours, cancel_deadline_hours, is_active, created_at, updated_at
├── リレーション: store, category, instructor, schedules

lesson_schedules (レッスンスケジュール) ✅
├── id, lesson_id, start_datetime, end_datetime, current_bookings, is_active, created_at, updated_at
├── リレーション: lesson, reservations

reservations (予約) ✅
├── id, user_id, lesson_schedule_id, user_subscription_id, status, reserved_at, created_at, updated_at
├── リレーション: user, lessonSchedule, userSubscription

user_favorites (ユーザーお気に入り) ✅
├── id, user_id, favoritable_type, favoritable_id, created_at, updated_at
├── リレーション: user, favoritable (多態的関連)
├── 制約: 同一ユーザーの重複お気に入り防止
```

#### 実装済み機能
- **階層構造**: レッスンカテゴリの親→子→孫対応
- **多態的関連**: お気に入り機能（店舗・インストラクター対応）
- **外部キー制約**: データ整合性の保証
- **インデックス**: パフォーマンス最適化
- **Eloquent モデル**: リレーションシップ・スコープ・アクセサ実装
- **バリデーション**: 適切なデータ型・制約設定

### 機能設計

#### ユーザー側機能
- アカウント登録・ログイン
- 月謝プラン選択・契約（Stripe決済）
- レッスン予約・キャンセル
- 予約履歴・利用状況確認
- 店舗・レッスン検索・表示
- お気に入り機能（店舗・インストラクター）

#### インストラクター側機能
- 自分のレッスン予約枠の作成・編集・削除
- 自分のレッスンへの予約一覧確認

#### 管理者側機能
- 店舗管理（複数店舗対応）
- レッスンカテゴリ管理（親カテゴリ・子カテゴリ階層）
- レッスン管理（パーソナル・グループ）
- 月謝プラン管理
- 予約状況確認・管理
- ユーザー・インストラクター管理

## 詳細要件

### 月謝プラン詳細

#### グループレッスン（ピラティス・ヨガ）
- **月1回券サブスク**: 3,300円（allowed_category_ids: [1, 2]）
- **月2回券サブスク**: 6,000円（allowed_category_ids: [1, 2]）
- **月4回券サブスク**: 11,000円（allowed_category_ids: [1, 2]）

#### パーソナルレッスン（パーソナルレッスン）
- **月1回券サブスク**: 4,000円（allowed_category_ids: [4]）
- **月2回券サブスク**: 8,000円（allowed_category_ids: [4]）
- **月4回券サブスク**: 12,000円（allowed_category_ids: [4]）

#### プラン仕様
- **支払い方法**: 毎月払い（契約日から1ヶ月ごと）
- **未使用分**: 繰り越しなし（月次リセット）
- **複数契約**: 1つのアカウントで複数プラン契約可能
- **予約代理**: 家族・友人への代理予約不可
- **レッスン制限**: サブスクリプションのカテゴリに応じたレッスンのみ予約可能

#### プラン制約
- **lesson_count**: 1以上（空や0は不可）
- **price**: 1円以上

#### Stripeとの紐付け例
```php
// 決済（Checkout）
$user->newSubscription('default', $plan->stripe_price_id)
    ->checkout([
        'success_url' => route('billing.success'),
        'cancel_url' => route('billing.cancel'),
    ]);

// Webhookでの同期（例）
public function handleInvoicePaid(array $payload): void
{
    $sub = $payload['data']['object'];
    $customer = $sub['customer'];
    $priceId = $sub['lines']['data'][0]['price']['id'] ?? null;

    $user = User::where('stripe_id', $customer)->first();
    $plan = SubscriptionPlan::where('stripe_price_id', $priceId)->first();

    if ($user && $plan) {
        UserSubscription::query()
            ->where('user_id', $user->id)
            ->where('plan_id', $plan->id)
            ->update(['current_month_used_count' => 0, 'payment_status' => 'paid']);
    }
}
```

### レッスン仕様

#### 基本仕様
- **基本時間**: 60分（設定可能）
- **レッスンカテゴリ**: 階層構造で管理（親カテゴリ：パーソナルレッスン・グループレッスン、子カテゴリ：各レッスン種別）
- **定員**: レッスンごとに個別設定
- **インストラクター**: レッスン内容により変更

#### 予約ルール
- **予約可能期間**: 各レッスン毎に設定可能、デフォルト24時間前
  - 例：9月15日 10:00開始のレッスン → 9月14日 10:00まで予約可能
  - 予約作成時点から予約可能、期限を過ぎると予約不可
- **キャンセル期限**: 各レッスン毎に設定可能、デフォルト24時間前
  - 期限を過ぎた場合：管理者・インストラクターのみキャンセル可能
  - ユーザー側：キャンセルボタンを無効化（押せない状態）
- **予約制限**: 同じ時間帯の重複予約防止
- **複数プラン契約時**: プラン間で予約可能内容が重複しないよう設計
  - 万が一重複した場合：ユーザーが選択（UIは実装時に決定）

#### インストラクター例
- Aさんのパーソナル
- グループヨガAさん担当
- グループヨガBさん担当

### 権限管理

#### ユーザー権限
- **一般ユーザー** (role: user): 予約・キャンセル・履歴確認
- **インストラクター** (role: instructor): 自分のレッスン管理・予約一覧確認
- **管理者** (role: admin): 全機能管理

#### インストラクター機能
- 自分のレッスン予約枠の作成・編集・削除
- 自分のレッスンへの予約一覧確認

#### セキュリティ対策
- **ロール変更制限**: roleフィールドはfillableに含めず、専用メソッドでのみ変更可能
- **認可制御**: Gates/Policiesによる多層防御
  - Gate定義（`App\Providers\AppServiceProvider`）
    - `access-dashboard`: 管理者/インストラクターのみ
    - `access-admin`: 管理者のみ
    - `access-instructor`: インストラクター/管理者
    - `manage-subscription-plans`: 管理者のみ
  - Policy実装（自動ディスカバリ）
    - `App\Policies\SubscriptionPlanPolicy`
      - `before`: 管理者は全許可
      - `viewAny`/`view`: 全ユーザー許可
      - `create`/`update`/`delete`/`restore`/`forceDelete`: 非管理者は不可
- **ルート保護**: ミドルウェアによる権限チェック
- **監査ログ**: ロール変更時の履歴記録
- **CSRF保護**: セッション設定によるCSRF攻撃防止
- **XSS防止**: Bladeテンプレートの自動エスケープ
- **SQLインジェクション防止**: Eloquent ORMのパラメータバインディング
- **セッションセキュリティ**: HTTPS専用・HTTPOnly・SameSite設定
- **レート制限**: API・フォーム送信の頻度制限
- **入力検証**: Form Requestによる厳密なバリデーション

## ページ構成

### メインページ
1. **トップページ**: アプリケーションのメインページ
   - パーソナルレッスンボタン → /reservations/personal
   - グループレッスンボタン → /reservations/group
2. **予約ページ**: レッスン予約・キャンセル機能
   - 基本URL: /reservations/{category}（personal|group）
   - 絞り込み: ?date=...&instructor=...&store=...（URLパラメータ）
3. **店舗一覧**: 登録店舗の一覧・詳細表示・お気に入り登録/解除
4. **インストラクター一覧**: インストラクターの一覧・詳細表示・お気に入り登録/解除
5. **マイページ**: ユーザー情報・予約履歴・サブスクリプション管理・お気に入り管理

### 認証・権限・UI要件
- **認証状態**: 全ページログイン必須
- **権限別表示**: ユーザー向けページは内容共通（ロールによる表示内容変更なし）
- **レスポンシブ**: モバイルファーストデザイン

*各ページの詳細仕様は後日記載予定*

#### ログイン後の遷移ルール（実装）
- 一般ユーザー（role: user）: `home` ルート（`/` トップページ）に固定遷移（`intended` は無視）
- 管理者 / インストラクター（role: admin|instructor）: `dashboard`

## 実装計画

### Phase 1: 基盤構築
- [x] Livewireインストール・設定
- [x] ユーザー認証システム（Laravel Breeze/Fortify）
- [x] 認証ミドルウェア設定（全ページログイン必須）
- [x] 権限管理システム（Gates/Policies）
- [x] データベース設計・マイグレーション
- [ ] 基本的なCRUD機能
  - [x] stores（店舗）CRUD
  - [ ] lesson_categories（レッスンカテゴリ）CRUD
  - [ ] lessons（レッスン）CRUD
  - [ ] lesson_schedules（レッスンスケジュール）CRUD
  - [ ] subscription_plans（月謝プラン）CRUD（Phase 2で実装）
- [ ] モバイルファーストUI基盤構築
- [ ] セキュリティ強化実装
  - [ ] CSRF保護設定
  - [ ] XSS防止設定
  - [ ] セッションセキュリティ設定
  - [ ] レート制限実装
  - [ ] 入力検証（Form Request）実装

### Phase 2: 月謝システム
- [ ] Laravel Cashierインストール・設定
- [ ] Stripe Products & Prices設定
- [ ] Stripe Checkout統合
- [ ] Webhook設定・自動同期
- [ ] サブスクリプション管理
- [ ] プラン管理機能
- [ ] 決済失敗時のエラーハンドリング
  - [ ] 段階的リトライ機能（最大3回、5秒→10秒→20秒）
  - [ ] ユーザー通知機能（画面表示のみ）
  - [ ] 手動再試行機能（ユーザー操作）

### Phase 3: 予約システム
- [ ] レッスン予約機能（Livewire）
  - [ ] カテゴリー別予約ページ（/reservations/{category}）
  - [ ] URLパラメータ絞り込み機能（日付・インストラクター・店舗）
- [ ] 予約制限・重複チェック
- [ ] 時間帯重複防止チェック
- [ ] 定員制限チェック
- [ ] 同一ユーザー重複防止
- [ ] 月謝制限チェック
- [ ] エラーハンドリング（ユーザーフレンドリーなメッセージ）
- [ ] キャンセル機能
- [ ] 通知機能（メール送信）
- [ ] リマインダー機能（24時間前）
  - [ ] レッスン開始時刻の24時間前にリマインダー送信
  - [ ] 例：9月15日 10:00開始 → 9月14日 10:00にリマインダー
- [ ] お気に入り機能（店舗・インストラクター）
  - [ ] 店舗一覧でのお気に入り登録/解除
  - [ ] インストラクター一覧でのお気に入り登録/解除
  - [ ] マイページでのお気に入り管理

#### Phase 3: ユーザー向けページ実装計画
##### 基本ページ構成
1. **トップページ** (`/`)
   - アプリケーションのメインページ
   - パーソナルレッスンボタン → `/reservations/personal`
   - グループレッスンボタン → `/reservations/group`
   - 店舗一覧ボタン → `/stores`
   - インストラクター一覧ボタン → `/instructors`

2. **認証ページ**
   - **ログインページ** (`/login`) - 既存のLaravel Breeze
   - **ユーザー登録ページ** (`/register`) - 既存のLaravel Breeze
   - **パスワードリセット** (`/forgot-password`) - 既存のLaravel Breeze
   - **メール認証** (`/verify-email`) - 既存のLaravel Breeze

3. **予約ページ** (`/reservations/{category}`)
   - 基本URL: `/reservations/{category}`（personal|group）
   - **グループレッスン** (`/reservations/group`):
     - 絞り込み機能（LAVAアプリ風）:
       - 店舗選択タブ（お気に入り店舗・全店舗・組み合わせ条件）
       - 組み合わせ条件: 店舗とレッスン内容の組み合わせ
       - 店舗ボタン（横スクロール可能）:
         - デフォルト: お気に入り店舗を全て表示
         - お気に入りなし: 店舗IDが低い順で表示
       - 日付選択（カレンダー表示・週単位ナビゲーション）
       - 時間帯表示（6時〜12時などの縦軸）
     - レッスン一覧表示:
       - デフォルト表示:
         - お気に入り店舗あり: お気に入り店舗全ての1週間分（店舗ID昇順）
         - お気に入りなし: 店舗ID最低の店舗の1週間分
       - 最大表示数: 15個
       - 時間帯別グループ化（17時、19時など）
       - レッスンカード（詳細情報 + アクションボタン）
       - 空き状況表示（オレンジ：空きあり、赤：満員/キャンセル待ち）
       - 予約状況（予約・キャンセル待ち予約）

   - **パーソナルレッスン** (`/reservations/personal`):
     - 絞り込み機能（LAVAアプリ風）:
       - インストラクター選択タブ（お気に入りインストラクター・全インストラクター・組み合わせ条件）
       - 組み合わせ条件: インストラクターと店舗の組み合わせ
       - インストラクターボタン（横スクロール可能）:
         - デフォルト: お気に入りインストラクターを全て表示
         - お気に入りなし: インストラクターIDが低い順で表示
       - 日付選択（カレンダー表示・週単位ナビゲーション）
       - 時間帯表示（6時〜12時などの縦軸）
     - レッスン一覧表示:
       - デフォルト表示:
         - お気に入りインストラクターあり: お気に入りインストラクター全ての予約枠（インストラクターID昇順）
         - お気に入りなし: 今日から日付の近い順で表示
       - 最大表示数: 15個
       - 時間帯別グループ化（17時、19時など）
       - レッスンカード（詳細情報 + アクションボタン）
       - 空き状況表示（オレンジ：空きあり、赤：満員/キャンセル待ち）
       - 予約状況（予約・キャンセル待ち予約）

   - カレンダー表示（Alpine.js）
   - 時間枠選択（Livewire）
   - 予約確認・完了フロー

4. **店舗一覧・詳細** (`/stores`, `/stores/{store}`)
   - 店舗一覧表示
   - 店舗詳細情報
   - お気に入り登録/解除
   - その店舗のレッスン一覧
   - 地図表示（Google Maps）

5. **インストラクター一覧・詳細** (`/instructors`, `/instructors/{instructor}`)
   - インストラクター一覧表示
   - インストラクター詳細情報
   - お気に入り登録/解除
   - そのインストラクターのレッスン一覧
   - プロフィール・経歴

6. **マイページ** (`/profile`)
   - ユーザー情報編集
   - 予約履歴一覧
   - サブスクリプション管理
   - お気に入り管理（店舗・インストラクター）
   - 利用状況・残り回数確認

7. **予約履歴詳細** (`/reservations/history`)
   - 過去の予約一覧
   - キャンセル履歴
   - 利用統計（月別・カテゴリ別）

8. **サブスクリプション管理** (`/subscriptions`)
   - 現在の契約プラン一覧
   - 利用状況・残り回数
   - プラン変更・解約
   - 決済履歴

9. **お気に入り管理** (`/favorites`)
   - お気に入り店舗一覧
   - お気に入りインストラクター一覧
   - お気に入り解除

##### 技術実装ポイント
- **Livewire活用**: 予約状況のリアルタイム更新、絞り込み検索
- **Alpine.js活用**: カレンダー操作、フォーム制御、UI状態管理
- **レスポンシブデザイン**: モバイルファースト、タブレット・デスクトップ対応
- **パフォーマンス**: 画像遅延読み込み、ページネーション、キャッシュ活用
- **UX向上**: ローディング状態、エラーハンドリング、成功メッセージ

#### Phase 4: 管理者・インストラクター向けページ実装計画
##### 管理者専用ページ
1. **管理者ダッシュボード** (`/dashboard`)
   - システム全体の統計情報
   - 売上・予約状況のサマリー
   - 最近のアクティビティ
   - アラート・通知

2. **店舗管理** (`/admin/stores`)
   - 店舗一覧・検索・フィルタリング
   - 店舗の作成・編集・削除
   - 店舗の有効/無効切り替え
   - 店舗別統計情報

3. **レッスンカテゴリ管理** (`/admin/lesson-categories`)
   - カテゴリ一覧（階層表示）
   - 親カテゴリ・子カテゴリの作成・編集・削除
   - 並び順の変更（ドラッグ&ドロップ）
   - カテゴリ別統計情報

4. **レッスン管理** (`/admin/lessons`)
   - レッスン一覧・検索・フィルタリング
   - レッスンの作成・編集・削除
   - レッスンの有効/無効切り替え
   - レッスン別予約状況

5. **レッスンスケジュール管理** (`/admin/lesson-schedules`)
   - スケジュール一覧・カレンダー表示
   - スケジュールの作成・編集・削除
   - 一括スケジュール作成
   - 予約状況の確認

6. **月謝プラン管理** (`/admin/subscription-plans`)
   - プラン一覧・検索・フィルタリング
   - プランの作成・編集・削除
   - Stripe連携設定
   - プラン別利用統計

7. **ユーザー管理** (`/admin/users`)
   - ユーザー一覧・検索・フィルタリング
   - ユーザー情報の編集
   - ロール変更（管理者のみ）
   - ユーザー別利用統計

8. **予約管理** (`/admin/reservations`)
   - 予約一覧・検索・フィルタリング
   - 予約の確認・キャンセル
   - 予約統計・レポート
   - 定員オーバー時の対応

9. **サブスクリプション管理** (`/admin/subscriptions`)
   - 契約一覧・検索・フィルタリング
   - 契約状況の確認
   - 決済状況の確認
   - 契約統計・レポート

10. **システム設定** (`/admin/settings`)
    - アプリケーション設定
    - 通知設定
    - セキュリティ設定
    - バックアップ・メンテナンス

##### インストラクター専用ページ
1. **インストラクターダッシュボード** (`/instructor/dashboard`)
   - 自分のレッスン予約状況
   - 今週・来週のスケジュール
   - 生徒からのフィードバック

2. **自分のレッスン管理** (`/instructor/lessons`)
   - 担当レッスン一覧
   - レッスン情報の編集
   - レッスンスケジュールの作成・編集

3. **予約確認** (`/instructor/reservations`)
   - 自分のレッスンへの予約一覧
   - 予約者情報の確認
   - キャンセル待ち状況

##### 技術実装ポイント
- **権限管理**: ロール別アクセス制御（Gates/Policies）
- **データテーブル**: 大量データの効率的表示・検索
- **リアルタイム更新**: Livewireによる予約状況の即座反映
- **バッチ処理**: 一括操作・スケジュール作成
- **レポート機能**: 統計情報・CSVエクスポート
- **監査ログ**: 管理者操作の履歴記録

## 重要な考慮事項

### ビジネスロジック
- **月謝の回数制限**: 契約プランに応じた予約回数制限
- **レッスン制限**: サブスクリプションのカテゴリに応じたレッスンのみ予約可能
- **時間帯重複防止**: 同じ時間帯の異なるレッスン間での予約競合防止
- **定員制限**: レッスン定員を超える予約の防止
- **同一ユーザー重複防止**: 同じユーザーの同一レッスンへの重複予約防止
- **キャンセルポリシー**: 各レッスン毎のキャンセル期限（デフォルト24時間前）
- **予約期限**: 各レッスン毎の予約可能期間（デフォルト24時間前）
- **店舗間の予約**: 複数店舗での予約管理
- **月次リセット**: 契約日から1ヶ月ごとの請求サイクルで未使用回数をリセット

### 技術的考慮事項
- **Stripe Webhook**: 決済状況の自動同期
- **Stripe Checkout**: セキュアな決済処理
- **予約の同時性**: 同時予約時の競合処理（定員・重複・月謝制限チェック）
- **エラーハンドリング**: ユーザーフレンドリーなエラーメッセージ
- **決済失敗処理**: 段階的リトライ・手動再試行・画面通知
- **通知機能**: 予約確認・リマインダー
- **レポート機能**: 利用状況・売上レポート
- **セキュリティ**: 多層防御による権限制御
- **バージョン管理**: GitHubによるコード管理・共同開発
- **データ管理**: 削除されたレッスンは物理削除、キャンセルされた予約データは保持

## 技術選択の理由

### Livewire選択の理由
1. **PHPのみで開発**: JavaScriptの知識が最小限で済む
2. **リアルタイム更新**: 予約状況の即座反映
3. **シンプルな学習曲線**: Laravel開発者にとって親しみやすい
4. **Alpine.js統合**: クライアントサイドの細かい制御も可能

### Laravel Cashier + Stripe Checkout選択の理由
1. **標準的なアプローチ**: Laravel Cashierの推奨方法
2. **自動同期**: Webhookでデータベースが自動更新
3. **セキュリティ**: 決済情報をアプリケーションで保持しない
4. **開発効率**: 実装時間が大幅に短縮（4-6時間 vs 26-38時間）
5. **保守性**: 長期的な運用が容易

### 予約システムでの活用例
- **カレンダー表示**: Alpine.jsで日付選択のインタラクション
- **時間枠選択**: Livewireでサーバーサイドの空き状況確認
- **リアルタイム更新**: 予約状況の即座反映
- **フォーム検証**: クライアント・サーバー両方でのバリデーション
- **決済処理**: Stripe Checkoutでセキュアな決済
- **自動同期**: Webhookでサブスクリプション状態を自動更新

## 通知・コミュニケーション（確定）

### 通知の種類
- **予約確認メール**: 予約完了時に送信
- **予約リマインダー**: 24時間前に送信
- **キャンセル通知**: キャンセル時に送信
- **サブスク更新通知**: Stripeからの通知 + アプリからの通知

### 送信設定
- **送信先**: 登録時のメールアドレス（プロフィール更新時は更新後のアドレス）
- **送信タイミング**: 各イベント発生時 + リマインダーは24時間前

## セキュリティ設定

### 環境変数設定
```env
# セキュリティ設定
APP_DEBUG=false
APP_ENV=production
APP_KEY=base64:your-32-character-key

# セッションセキュリティ
SESSION_SECURE_COOKIE=true
SESSION_HTTP_ONLY=true
SESSION_SAME_SITE=lax

# データベースセキュリティ
DB_STRICT=true
```

### セキュリティ設定ファイル
```php
// config/session.php
'secure' => env('SESSION_SECURE_COOKIE', true),
'http_only' => env('SESSION_HTTP_ONLY', true),
'same_site' => env('SESSION_SAME_SITE', 'lax'),

// config/auth.php
'password_timeout' => 10800, // 3時間
'passwords' => [
    'throttle' => 60, // 60秒間隔
    'expire' => 60,   // 60分で期限切れ
],
```

### セキュリティ実装例
```php
// レート制限ミドルウェア
Route::middleware(['throttle:6,1'])->group(function () {
    Route::post('/login', [AuthController::class, 'login']);
    Route::post('/register', [AuthController::class, 'register']);
});

// Form Request バリデーション
class ReservationRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'lesson_schedule_id' => 'required|exists:lesson_schedules,id',
            'user_subscription_id' => 'required|exists:user_subscriptions,id',
        ];
    }
}
```

## 開発環境

### 開発環境
- **Laravel Head**: 開発環境として使用
- **GitHub**: バージョン管理・コード共有
- **PHP**: 8.4.11
- **Laravel**: 12.26.3
- **データベース**: SQLite（開発）

### 必要なパッケージ
```bash
# Livewire
composer require livewire/livewire

# Laravel Cashier (Stripe統合)
composer require laravel/cashier

# 認証（選択）
composer require laravel/breeze --dev
# または
composer require laravel/fortify
```

### 開発コマンド
```bash
# 開発サーバー起動
php artisan serve

# フロントエンド開発サーバー
npm run dev

# データベース操作
php artisan migrate
php artisan migrate:fresh --seed
php artisan db:seed

# テスト実行
php artisan test
php artisan test --coverage

# コードフォーマット
vendor/bin/pint

# セキュリティチェック
composer audit

# キャッシュクリア
php artisan cache:clear
php artisan config:clear
php artisan route:clear
php artisan view:clear

## Git運用

### ブランチ戦略
- **main**: 本番環境用（安定版）
- **develop**: 開発統合用
- **feature/**: 新機能開発用
- **hotfix/**: 緊急修正用

### コミットメッセージ規約
```
feat: 新機能追加
fix: バグ修正
docs: ドキュメント更新
style: コードフォーマット
refactor: リファクタリング
test: テスト追加・修正
chore: その他の変更
```

### 開発フロー
1. **feature/ブランチ作成** → 機能開発 → **developにマージ** → **mainにリリース**
2. **各機能は独立したブランチ**で開発
3. **PR（Pull Request）**でコードレビュー
4. **コミットは小さく、意味のある単位**で分割

### Git操作コマンド
```bash
# 基本操作
git add .
git commit -m "feat: 機能追加"
git push origin main

# ブランチ操作
git checkout -b feature/新機能名
git checkout develop
git merge feature/新機能名

# 開発フロー例
git checkout -b feature/user-authentication
# 実装作業...
git add .
git commit -m "feat: ユーザー認証システム実装"
git push origin feature/user-authentication
# PR作成 → developマージ
```
```

---

**作成日**: 2025年9月
**バージョン**: 1.3
**ステータス**: 要件定義完了・実装準備完了
