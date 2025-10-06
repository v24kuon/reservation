# ヨガ・ピラティス教室予約システム

## プロジェクト概要

ヨガやピラティス等の教室の予約を自動化するシステムです。

### 主要機能
- **月謝制予約システム**: Stripeによるサブスクリプション決済
- **複数店舗対応**: 店舗ごとのレッスン管理
- **レッスン種類**: パーソナル・グループレッスン対応
- **予約管理**: 月謝プランに応じた予約回数制限
- **お気に入り機能**: 店舗・インストラクターのお気に入り登録
- **予約履歴・キャンセル**: ユーザー予約履歴表示とキャンセル機能（期限チェック、重複防止）
- **管理機能**: レッスンスケジュール一括作成・管理、予約状況確認
- **エラーハンドリング**: フラッシュメッセージ表示、キャンセル不可理由表示
- **タイムゾーン対応**: スケジュール生成時のタイムゾーン調整（JST準拠）

## 技術スタック

### バックエンド
- **PHP**: 8.4.11
- **Laravel Framework**: 12.26.3
- **データベース**:
  - 開発環境: SQLite
  - 本番環境: MySQL

### フロントエンド
- **基本**: Laravel Blade（サーバーサイドレンダリング）
- **動的機能**: Livewire（予約ページなど）
- **クライアントサイド**: Alpine.js（Livewireバンドル）

### 決済システム
- **Stripe**: サブスクリプション決済
- **Laravel Cashier**: Stripe統合ライブラリ
- **Stripe Checkout**: 決済処理
- **Webhook**: 自動同期

#### Stripe連携とプラン紐付け（指針）
- プラン定義はアプリ（`subscription_plans`）に保持し、請求は常に`stripe_price_id`を用いる（アプリ側の`price`は表示用）
- Stripeで作成したProduct/PriceのID（`stripe_product_id`/`stripe_price_id`）を管理画面からプランに登録し、一意制約を付与する
- 決済はStripe Checkoutを優先（保存済みPM向けの`->create()`ではなく`->checkout([...])`を使用）
- 契約作成後はWebhookで同期：`checkout.session.completed`/`customer.subscription.created`/`invoice.paid`/`invoice.payment_failed`などを処理
- 利用回数リセットは`invoice.paid`で当期開始時に`current_month_used_count=0`へ更新
- 監査性確保のため、`user_subscriptions`に当期の`stripe_price_id`（任意で`monthly_quota`）も保存
- 管理画面入力バリデーション：`stripe_product_id`は`/^prod_/`、`stripe_price_id`は`/^price_/`にマッチ必須

### 開発環境
- **Laravel Head**: 開発環境
- **GitHub**: バージョン管理・コード共有

## セットアップ

### 必要な環境
- PHP 8.4.11以上
- Composer
- Node.js & npm
- Git

### インストール手順

1. **リポジトリのクローン**
```bash
git clone [repository-url]
cd reservation
```

2. **依存関係のインストール**
```bash
composer install
npm install
```

3. **環境設定**
```bash
cp .env.example .env
php artisan key:generate
```

4. **データベース設定**
```bash
# .envファイルでデータベース設定
DB_CONNECTION=sqlite
DB_DATABASE=/path/to/database.sqlite

# データベースファイル作成
touch database/database.sqlite
```

5. **マイグレーション実行**
```bash
php artisan migrate
php artisan db:seed
```

6. **Stripe設定**
```bash
# .envファイルにStripe設定を追加
STRIPE_KEY=your-stripe-publishable-key
STRIPE_SECRET=your-stripe-secret-key
STRIPE_WEBHOOK_SECRET=your-webhook-secret
```

## 開発コマンド

### 開発サーバー
```bash
# Laravel開発サーバー
php artisan serve

# フロントエンド開発サーバー
npm run dev
```

### データベース操作
```bash
# マイグレーション実行
php artisan migrate

# データベースリセット・シード実行
php artisan migrate:fresh --seed

# シードデータのみ実行
php artisan db:seed
```

### テスト実行
```bash
# 基本テスト
php artisan test

# カバレッジ付きテスト
php artisan test --coverage
```

### コード品質管理
```bash
# コードフォーマット
vendor/bin/pint

# セキュリティチェック
composer audit
```

### キャッシュ管理
```bash
# 各種キャッシュクリア
php artisan cache:clear
php artisan config:clear
php artisan route:clear
php artisan view:clear
```

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

## プロジェクト構造

### 主要ページ
1. **トップページ**: アプリケーションのメインページ
2. **予約ページ**: レッスン予約・キャンセル機能（グループ/パーソナル）
3. **店舗一覧**: 登録店舗の一覧・詳細表示、お気に入り登録
4. **インストラクター一覧**: インストラクターの一覧・詳細表示、お気に入り登録
5. **マイページ**: ユーザー情報・予約履歴・サブスクリプション管理
6. **管理画面**: レッスン/スケジュール/予約/ユーザー管理

### データベース設計
- **users**: ユーザー管理（一般・インストラクター・管理者）
- **stores**: 店舗情報
- **lesson_categories**: レッスンカテゴリ（階層構造）
- **subscription_plans**: 月謝プラン（stripe_product_id, stripe_price_id含む）
- **user_subscriptions**: ユーザーの月謝契約
- **lessons**: レッスン情報
- **lesson_schedules**: レッスンスケジュール
- **reservations**: 予約情報
- **user_favorites**: ユーザーお気に入り

### 月謝プラン詳細
- **グループレッスン**: 月1回3,300円、月2回6,000円、月4回11,000円
- **パーソナルレッスン**: 月1回4,000円、月2回8,000円、月4回12,000円
- **複数契約**: 1アカウントで複数プラン契約可能
- **制限**: カテゴリ別レッスン制限、月次リセット

## 最近の変更点
- **タイムゾーン修正**: スケジュール一括生成時の時刻ズレ（+9h）を解消。datetime-local入力とサーバー生成をJST準拠に調整。
- **予約重複防止強化**: キャンセル時のstatus=canceled重複をトランザクション内で事前削除し、一意制約違反を回避。
- **UI改善**: 予約完了/キャンセル時のフラッシュメッセージ表示、キャンセル不可時の理由表示（期限超過など）。
- **エラーハンドリング**: ユーザー向けエラーメッセージを明確化（例: 二重予約、満員、期限超過）。

## 開発フェーズ

### Phase 1: 基盤構築
- [x] Livewireインストール・設定
- [x] ユーザー認証システム（Laravel Breeze/Fortify）
- [x] 認証ミドルウェア設定（全ページログイン必須）
- [x] 権限管理システム（Gates/Policies）
- [x] データベース設計・マイグレーション
- [x] 基本的なCRUD機能
- [x] モバイルファーストUI基盤構築
- [x] セキュリティ強化実装
  - [x] CSRF保護設定
  - [x] XSS防止設定
  - [x] セッションセキュリティ設定
  - [x] レート制限実装
  - [x] 入力検証（Form Request）実装

### Phase 2: 月謝システム
- [x] Laravel Cashierインストール・設定
- [x] Stripeキー設定: `.env(.example)` に `STRIPE_KEY`, `STRIPE_SECRET`, `STRIPE_WEBHOOK_SECRET` を追加
- [x] Stripe Products & Prices設定
  - [x] 管理画面でのStripe Price ID自動取得機能
  - [x] 価格バリデーション（JPY、recurring、active状態チェック）
- [x] Stripe Checkout統合
  - [x] サブスクリプション作成・更新・キャンセル機能
  - [x] プラン切り替え時の残数移行機能
- [x] Webhook設定・自動同期
  - [x] StripeWebhookListener実装
  - [x] 専用キュー（stripe-webhooks）での処理
  - [x] 冪等性を保つWebhook処理
  - [x] 回数制限管理とWebhook同期
  - [x] 時刻境界の注意（Stripe period_start 基準、JST表示とのズレ回避）
  - [x] 運用要件:
    - [x] Stripe-Signature 検証必須（署名の時刻猶予も指定）
    - [x] event.id の一意記録で再送対策
    - [x] stripe-webhooks キューの専用ワーカー/再試行ポリシー（最大試行、バックオフ）
- [x] サブスクリプション管理
  - [x] 回数制限管理機能（remaining_lessons、current_month_used_count）
  - [x] 月次リセット（invoice.paid で current_month_used_count=0 にリセット）
  - [x] プラン切替時の残数再計算（新プランの lesson_count に設定）
  - [x] プラン切り替え時の残数移行ログ
- [x] subscription_plans（月謝プラン）CRUD実装
  - [x] 管理画面でのプラン管理
  - [x] Stripe連携バリデーション
- [x] 決済失敗時のエラーハンドリング
  - [x] Webhook経由での決済失敗処理
  - [x] エラーログとユーザー通知

### Phase 3: 予約システム
- [x] 予約データモデル構築
  - [x] Reservationモデルとマイグレーション作成
  - [x] 二重予約防止制約（user_id + lesson_schedule_id + status のユニーク制約）
  - [x] 予約制約バリデーション機能
  - [x] キャンセル期限チェック機能
  - [x] サーバー側バリデーション強制（UI無効化に加えて）
- [x] レッスン予約機能（Livewire）
  - [x] グループ/パーソナル予約ページ（/reservations/group, /reservations/personal）
  - [x] URLパラメータ絞り込み機能（日付・インストラクター・店舗、お気に入り優先）
- [x] 予約制限・重複チェック
- [x] 時間帯重複防止チェック
- [x] 定員制限チェック
- [x] 同一ユーザー重複防止（予約済み表示、無効化）
- [x] 月謝制限チェック（残り回数表示）
- [x] エラーハンドリング（ユーザーフレンドリーなメッセージ、フラッシュ表示）
- [x] キャンセル機能（期限チェック、重複canceled削除）
- [x] 通知機能（メール送信、Webhook連携）
- [x] リマインダー機能（24時間前、Webhook予定）
- [x] お気に入り機能（店舗・インストラクター）
  - [x] 店舗一覧でのお気に入り登録/解除
  - [x] インストラクター一覧でのお気に入り登録/解除
  - [x] マイページでのお気に入り管理

### Phase 4: 管理機能
- [x] 管理者ダッシュボード
- [x] レッスンカテゴリ管理（階層構造）
- [x] 店舗・レッスン管理
- [x] 予約状況確認（管理予約一覧、フィルタ）
- [x] スケジュール一括作成（繰り返し生成、タイムゾーン対応）

## ライセンス

このプロジェクトは非公開プロジェクトです。

---
**作成日**: 2025年9月
**バージョン**: 1.4
**ステータス**: 実装完了・運用準備中
