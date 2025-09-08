# Requirements Document for Yoga & Pilates Reservation System

## Introduction

### English
This system is designed to automate the reservation process for yoga and pilates studios. It provides a comprehensive platform for managing multiple studios, various lesson types (personal and group lessons), subscription-based reservations, and administrative functions. The system integrates with Stripe for payment processing and supports both user and instructor roles.

### 日本語
このシステムは、ヨガ・ピラティス教室の予約プロセスを自動化することを目的としています。複数のスタジオ管理、各種レッスンタイプ（パーソナルレッスン・グループレッスン）、サブスクリプションベースの予約管理、管理機能などを包括的に提供します。Stripeとの決済連携を備え、ユーザー・インストラクター・管理者の各ロールをサポートします。

## Alignment with Product Vision

### English
This feature supports the core business goals of streamlining reservation management for yoga/pilates studios, providing a user-friendly interface for customers, and enabling efficient administrative control over multiple studio operations.

### 日本語
この機能は、ヨガ・ピラティス教室の予約管理を効率化し、顧客にとって使いやすいインターフェースを提供し、複数スタジオ運営の効率的な管理を可能にするという、製品の核心となるビジネス目標をサポートします。

## Requirements

### Requirement 1: User Authentication and Authorization

#### English
**User Story:** As a user/instructor/admin, I want to securely authenticate and access role-appropriate features, so that I can use the system according to my permissions.

##### Acceptance Criteria
1. WHEN a user registers THEN the system SHALL create an account with role-based access control
2. WHEN a user logs in THEN the system SHALL redirect them to the appropriate dashboard based on their role
3. WHEN an unauthorized user attempts to access admin functions THEN the system SHALL deny access and redirect to login
4. IF a user has role 'user' THEN the system SHALL show only reservation and profile features
5. IF a user has role 'instructor' THEN the system SHALL show lesson management and reservation overview features
6. IF a user has role 'admin' THEN the system SHALL show full administrative controls

#### 日本語
**ユーザーストーリー:** ユーザー/インストラクター/管理者として、安全に認証し、自分の権限に応じた機能にアクセスしたい。自分の権限に応じてシステムを利用するため。

##### 受け入れ条件
1. ユーザーが登録したとき、システムはロールベースのアクセス制御を持つアカウントを作成する
2. ユーザーがログインしたとき、システムはロールに応じて適切なダッシュボードにリダイレクトする
3. 権限のないユーザーが管理者機能にアクセスしようとしたとき、システムはアクセスを拒否しログインページにリダイレクトする
4. ユーザーのロールが'user'の場合、システムは予約とプロフィール機能のみを表示する
5. ユーザーのロールが'instructor'の場合、システムはレッスン管理と予約概要機能を表示する
6. ユーザーのロールが'admin'の場合、システムは完全な管理者機能を表示する

### Requirement 2: Store Management

#### English
**User Story:** As an administrator, I want to manage multiple studio locations, so that I can operate across different geographical areas.

##### Acceptance Criteria
1. WHEN an admin creates a store THEN the system SHALL save store details including name, address, phone, access info, parking, and Google Maps URL
2. WHEN an admin updates a store THEN the system SHALL validate and save changes while maintaining data integrity
3. WHEN an admin deactivates a store THEN the system SHALL prevent new lessons from being scheduled there
4. WHEN a user views stores THEN the system SHALL display active stores with complete information

#### 日本語
**ユーザーストーリー:** 管理者として、複数のスタジオ拠点を管理したい。異なる地理的エリアで運営するため。

##### 受け入れ条件
1. 管理者が店舗を作成したとき、システムは名前、住所、電話、アクセス情報、駐車場情報、Google Maps URLを含む店舗詳細を保存する
2. 管理者が店舗を更新したとき、システムはデータを検証し、データ整合性を維持しながら変更を保存する
3. 管理者が店舗を無効化したとき、システムはその店舗で新しいレッスンをスケジュールできないようにする
4. ユーザーが店舗を表示したとき、システムは有効な店舗を完全な情報とともに表示する

### Requirement 3: Lesson Category Management

#### English
**User Story:** As an administrator, I want to organize lessons into hierarchical categories, so that users can easily find and filter lessons by type.

##### Acceptance Criteria
1. WHEN an admin creates a parent category THEN the system SHALL support "Group Lessons" and "Personal Lessons" as predefined categories
2. WHEN an admin creates a child category THEN the system SHALL associate it with a parent category (Yoga, Pilates, etc.)
3. WHEN an admin sets sort order THEN the system SHALL display categories in the specified order
4. WHEN a category is deactivated THEN the system SHALL prevent new lessons from using that category

#### 日本語
**ユーザーストーリー:** 管理者として、レッスンを階層的なカテゴリに整理したい。ユーザーがタイプ別に見つけやすくフィルタリングできるようにするため。

##### 受け入れ条件
1. 管理者が親カテゴリを作成したとき、システムは「グループレッスン」と「パーソナルレッスン」を事前定義されたカテゴリとしてサポートする
2. 管理者が子カテゴリを作成したとき、システムはそれを親カテゴリ（ヨガ、ピラティスなど）と関連付ける
3. 管理者が並び順を設定したとき、システムは指定された順序でカテゴリを表示する
4. カテゴリが無効化されたとき、システムはそのカテゴリを使用した新しいレッスンを作成できないようにする

### Requirement 4: Lesson Management

#### English
**User Story:** As an administrator, I want to create and manage lesson definitions, so that instructors can schedule specific classes.

##### Acceptance Criteria
1. WHEN an admin creates a lesson THEN the system SHALL associate it with a store, category, instructor, and capacity
2. WHEN an admin sets duration THEN the system SHALL use it for scheduling calculations
3. WHEN an admin configures booking deadlines THEN the system SHALL enforce reservation time limits
4. WHEN an admin sets cancel deadlines THEN the system SHALL control cancellation permissions based on timing

#### 日本語
**ユーザーストーリー:** 管理者として、レッスンの定義を作成・管理したい。インストラクターが特定のクラスをスケジュールできるようにするため。

##### 受け入れ条件
1. 管理者がレッスンを作成したとき、システムはそれを店舗、カテゴリ、インストラクター、定員に関連付ける
2. 管理者が所要時間を設定したとき、システムはそれをスケジューリング計算に使用する
3. 管理者が予約締切を設定したとき、システムは予約時間の制限を強制する
4. 管理者がキャンセル締切を設定したとき、システムはタイミングに基づいてキャンセルの権限を制御する

### Requirement 5: Lesson Schedule Management

#### English
**User Story:** As an administrator, I want to create specific time slots for lessons, so that users can book at specific times.

##### Acceptance Criteria
1. WHEN an admin creates a schedule THEN the system SHALL associate it with a lesson and specific datetime
2. WHEN a user books a slot THEN the system SHALL increment current_bookings count
3. WHEN current_bookings reaches capacity THEN the system SHALL prevent further bookings
4. WHEN a schedule is deactivated THEN the system SHALL cancel existing reservations and prevent new bookings

#### 日本語
**ユーザーストーリー:** 管理者として、レッスンの特定の時間枠を作成したい。ユーザーが特定の時間に予約できるようにするため。

##### 受け入れ条件
1. 管理者がスケジュールを作成したとき、システムはそれをレッスンと特定の日時に関連付ける
2. ユーザーが枠を予約したとき、システムは現在の予約数を増加させる
3. 現在の予約数が定員に達したとき、システムはそれ以上の予約を防止する
4. スケジュールが無効化されたとき、システムは既存の予約をキャンセルし、新しい予約を防止する

### Requirement 6: Subscription Plan Management

#### English
**User Story:** As an administrator, I want to create subscription plans with Stripe integration, so that users can purchase recurring access to lessons.

##### Acceptance Criteria
1. WHEN an admin creates a plan THEN the system SHALL associate it with Stripe Product and Price IDs
2. WHEN an admin sets lesson_count THEN the system SHALL enforce monthly usage limits
3. WHEN an admin configures allowed_category_ids THEN the system SHALL restrict booking to specific lesson types
4. WHEN a plan is deactivated THEN the system SHALL prevent new subscriptions while maintaining existing ones

#### 日本語
**ユーザーストーリー:** 管理者として、Stripe連携によるサブスクリプションプランを作成したい。ユーザーがレッスンへの定期アクセスを購入できるようにするため。

##### 受け入れ条件
1. 管理者がプランを作成したとき、システムはそれをStripeのProduct IDとPrice IDに関連付ける
2. 管理者がlesson_countを設定したとき、システムは月次の利用制限を強制する
3. 管理者がallowed_category_idsを設定したとき、システムは予約を特定のレッスンタイプに制限する
4. プランが無効化されたとき、システムは新しいサブスクリプションを防止しつつ既存のものを維持する

### Requirement 7: User Subscription Management

#### English
**User Story:** As a user, I want to subscribe to plans and manage my subscription status, so that I can access lessons according to my plan.

##### Acceptance Criteria
1. WHEN a user subscribes to a plan THEN the system SHALL create a user_subscription record with Stripe integration
2. WHEN Stripe processes payment THEN the system SHALL update payment_status and subscription status
3. WHEN subscription period ends THEN the system SHALL reset current_month_used_count to zero
4. WHEN a user exceeds lesson_count THEN the system SHALL prevent further bookings until next period

#### 日本語
**ユーザーストーリー:** ユーザーとして、プランに加入しサブスクリプションステータスを管理したい。自分のプランに応じてレッスンにアクセスできるようにするため。

##### 受け入れ条件
1. ユーザーがプランに加入したとき、システムはStripe連携によるuser_subscriptionレコードを作成する
2. Stripeが決済を処理したとき、システムはpayment_statusとsubscriptionステータスを更新する
3. サブスクリプション期間が終了したとき、システムはcurrent_month_used_countをゼロにリセットする
4. ユーザーがlesson_countを超えたとき、システムは次の期間までそれ以上の予約を防止する

### Requirement 8: Reservation System

#### English
**User Story:** As a user, I want to book lessons according to my subscription plan, so that I can attend classes at my preferred times.

##### Acceptance Criteria
1. WHEN a user attempts to book THEN the system SHALL check subscription status and available slots
2. WHEN booking deadline is exceeded THEN the system SHALL reject the reservation
3. WHEN capacity is reached THEN the system SHALL add user to waitlist or reject booking
4. WHEN a user cancels THEN the system SHALL check cancel deadline and permissions
5. WHEN reservation is created THEN the system SHALL decrement available slots

#### 日本語
**ユーザーストーリー:** ユーザーとして、自分のサブスクリプションプランに応じてレッスンを予約したい。希望する時間にクラスに参加できるようにするため。

##### 受け入れ条件
1. ユーザーが予約しようとしたとき、システムはサブスクリプションステータスと利用可能な枠を確認する
2. 予約締切を超えたとき、システムは予約を拒否する
3. 定員に達したとき、システムはユーザーをキャンセル待ちに追加するか予約を拒否する
4. ユーザーがキャンセルしたとき、システムはキャンセル締切と権限を確認する
5. 予約が作成されたとき、システムは利用可能な枠を減少させる

### Requirement 9: Favorites System

#### English
**User Story:** As a user, I want to favorite stores and instructors, so that I can quickly access my preferred options.

##### Acceptance Criteria
1. WHEN a user favorites an item THEN the system SHALL create a polymorphic relationship
2. WHEN a user views favorites THEN the system SHALL display stores and instructors in separate sections
3. WHEN a user unfavorites an item THEN the system SHALL remove the relationship
4. WHEN displaying lesson options THEN the system SHALL prioritize favorite stores/instructors

#### 日本語
**ユーザーストーリー:** ユーザーとして、店舗とインストラクターをお気に入り登録したい。自分の好みのオプションに素早くアクセスできるようにするため。

##### 受け入れ条件
1. ユーザーがアイテムをお気に入りにしたとき、システムはポリモーフィック関連を作成する
2. ユーザーがお気に入りを表示したとき、システムは店舗とインストラクターを別々のセクションで表示する
3. ユーザーがアイテムをお気に入りから外したとき、システムはその関連を削除する
4. レッスンオプションを表示するとき、システムはお気に入りの店舗/インストラクターを優先的に表示する

### Requirement 10: Notification Template Management

#### English
**User Story:** As an administrator, I want to manage notification templates, so that the system can send appropriate messages for different events.

##### Acceptance Criteria
1. WHEN an admin creates a template THEN the system SHALL validate template type uniqueness
2. WHEN the system sends a notification THEN it SHALL use the appropriate template with variable substitution
3. WHEN template variables are used THEN the system SHALL substitute actual values (user name, lesson name, etc.)
4. WHEN a template is deactivated THEN the system SHALL use a fallback or skip notification

#### 日本語
**ユーザーストーリー:** 管理者として、通知テンプレートを管理したい。システムがさまざまなイベントに対して適切なメッセージを送信できるようにするため。

##### 受け入れ条件
1. 管理者がテンプレートを作成したとき、システムはテンプレートタイプの一意性を検証する
2. システムが通知を送信したとき、システムは適切なテンプレートを変数置換とともに使用する
3. テンプレート変数が使用されたとき、システムは実際の値（ユーザー名、レッスン名など）を置換する
4. テンプレートが無効化されたとき、システムはフォールバックを使用するか通知をスキップする

### Requirement 11: System Settings Management

#### English
**User Story:** As an administrator, I want to configure system-wide settings, so that I can customize the application's behavior.

##### Acceptance Criteria
1. WHEN an admin updates a setting THEN the system SHALL validate the value according to its type
2. WHEN the application loads THEN the system SHALL cache settings for performance
3. WHEN a setting key conflicts THEN the system SHALL prevent duplicate keys
4. WHEN settings are accessed THEN the system SHALL return appropriate data types

#### 日本語
**ユーザーストーリー:** 管理者として、システム全体の設定を構成したい。アプリケーションの動作をカスタマイズできるようにするため。

##### 受け入れ条件
1. 管理者が設定を更新したとき、システムはそのタイプに応じて値を検証する
2. アプリケーションが読み込まれたとき、システムはパフォーマンスのために設定をキャッシュする
3. 設定キーが競合したとき、システムは重複キーを防止する
4. 設定にアクセスされたとき、システムは適切なデータタイプを返す

## Non-Functional Requirements

### Code Architecture and Modularity

#### English
- **Single Responsibility Principle**: Each model, controller, and service should have a single, well-defined purpose
- **Modular Design**: Components should be isolated and reusable across the application
- **Dependency Management**: Minimize interdependencies between modules using proper service injection
- **Clear Interfaces**: Define clean contracts between components and layers

#### 日本語
- **単一責任の原則**: 各モデル、コントローラー、サービスは単一の明確に定義された目的を持つべき
- **モジュール設計**: コンポーネントはアプリケーション全体で分離され、再利用可能であるべき
- **依存関係管理**: 適切なサービスインジェクションを使用してモジュール間の相互依存を最小限に
- **明確なインターフェース**: コンポーネントとレイヤー間に明確な契約を定義

### Performance

#### English
- **Database Optimization**: Use proper indexing on frequently queried columns (start_datetime, user_id, etc.)
- **Query Optimization**: Implement eager loading to prevent N+1 query problems
- **Caching Strategy**: Cache frequently accessed data like settings and category hierarchies
- **Response Time**: API responses should complete within 500ms for most operations

#### 日本語
- **データベース最適化**: 頻繁にクエリされるカラム（start_datetime、user_idなど）に適切なインデックスを使用
- **クエリ最適化**: N+1クエリ問題を防ぐためにeager loadingを実装
- **キャッシュ戦略**: 設定やカテゴリ階層などの頻繁にアクセスされるデータをキャッシュ
- **応答時間**: ほとんどの操作でAPI応答は500ms以内に完了すべき

### Security

#### English
- **Authentication**: All endpoints require proper authentication and authorization
- **Input Validation**: All user inputs must be validated using Form Requests
- **CSRF Protection**: Enable CSRF protection for all state-changing operations
- **XSS Prevention**: Use Blade's automatic escaping and proper output sanitization
- **SQL Injection Prevention**: Use Eloquent ORM and parameterized queries exclusively
- **Rate Limiting**: Implement throttling on authentication and booking endpoints

#### 日本語
- **認証**: すべてのエンドポイントで適切な認証と認可が必要
- **入力検証**: すべてのユーザー入力はForm Requestを使用して検証必須
- **CSRF保護**: 状態変更操作すべてでCSRF保護を有効化
- **XSS防止**: Bladeの自動エスケープと適切な出力サニタイズを使用
- **SQLインジェクション防止**: Eloquent ORMとパラメータ化クエリのみを使用
- **レート制限**: 認証と予約エンドポイントにスロットリングを実装

### Reliability

#### English
- **Error Handling**: Comprehensive error handling with user-friendly messages
- **Data Integrity**: Foreign key constraints and database transactions for critical operations
- **Backup Strategy**: Regular database backups and recovery procedures
- **Monitoring**: Application logging and error tracking for system health

#### 日本語
- **エラーハンドリング**: ユーザーフレンドリーなメッセージによる包括的なエラーハンドリング
- **データ整合性**: 重要な操作に対する外部キー制約とデータベーストランザクション
- **バックアップ戦略**: 定期的なデータベースバックアップと回復手順
- **監視**: アプリケーションのログ記録とシステム健全性のためのエラー追跡

### Usability

#### English
- **Responsive Design**: Mobile-first approach with proper desktop support
- **Intuitive Navigation**: Clear information hierarchy and consistent UI patterns
- **Loading States**: Visual feedback for all asynchronous operations
- **Error Messages**: Clear, actionable error messages in Japanese
- **Accessibility**: WCAG 2.1 AA compliance for broad accessibility support

#### 日本語
- **レスポンシブデザイン**: 適切なデスクトップサポートによるモバイルファーストアプローチ
- **直感的なナビゲーション**: 明確な情報階層と一貫したUIパターン
- **ローディング状態**: すべての非同期操作に対する視覚的フィードバック
- **エラーメッセージ**: 日本語での明確で実行可能なエラーメッセージ
- **アクセシビリティ**: WCAG 2.1 AA準拠による幅広いアクセシビリティサポート
