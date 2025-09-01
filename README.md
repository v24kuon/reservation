# ヨガ・ピラティス教室予約システム

## プロジェクト概要

ヨガやピラティス等の教室の予約を自動化するシステムです。

### 主要機能
- **月謝制予約システム**: Stripeによるサブスクリプション決済
- **複数店舗対応**: 店舗ごとのレッスン管理
- **レッスン種類**: パーソナル・グループレッスン対応
- **予約管理**: 月謝プランに応じた予約回数制限
- **お気に入り機能**: 店舗・インストラクターのお気に入り登録

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
2. **予約ページ**: レッスン予約・キャンセル機能
3. **店舗一覧**: 登録店舗の一覧・詳細表示
4. **インストラクター一覧**: インストラクターの一覧・詳細表示
5. **マイページ**: ユーザー情報・予約履歴・サブスクリプション管理

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

## セキュリティ

### 実装済みセキュリティ対策
- **CSRF保護**: セッション設定によるCSRF攻撃防止
- **XSS防止**: Bladeテンプレートの自動エスケープ
- **SQLインジェクション防止**: Eloquent ORMのパラメータバインディング
- **セッションセキュリティ**: HTTPS専用・HTTPOnly・SameSite設定
- **レート制限**: API・フォーム送信の頻度制限
- **入力検証**: Form Requestによる厳密なバリデーション

## 開発フェーズ

### Phase 1: 基盤構築
- [ ] Livewireインストール・設定
- [ ] ユーザー認証システム（Laravel Breeze/Fortify）
- [ ] 認証ミドルウェア設定（全ページログイン必須）
- [ ] 権限管理システム（Gates/Policies）
- [ ] データベース設計・マイグレーション
- [ ] 基本的なCRUD機能
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
- [ ] お気に入り機能（店舗・インストラクター）
  - [ ] 店舗一覧でのお気に入り登録/解除
  - [ ] インストラクター一覧でのお気に入り登録/解除
  - [ ] マイページでのお気に入り管理

### Phase 4: 管理機能
- [ ] 管理者ダッシュボード
- [ ] レッスンカテゴリ管理（階層構造）
- [ ] 店舗・レッスン管理
- [ ] 予約状況確認

## ライセンス

このプロジェクトは非公開プロジェクトです。

---

**作成日**: 2025年9月
**バージョン**: 1.3
**ステータス**: 要件定義完了・実装準備完了
