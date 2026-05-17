# 模擬案件「勤怠管理システム」

## 概要

プログラミング学習のための勤怠管理システム。一般ユーザー（スタッフ）は勤怠登録（打刻）、勤怠一覧確認、勤怠修正申請などの機能を使用でき、管理者ユーザーは一般ユーザーの勤怠情報の閲覧や修正、修正申請の確認などができる。

## 使用技術

- Laravel 13.7.0
- PHP 8.5
- Mysql 8.4.9
- Mailpit v1.29.7
- Node 24

## ER図

```mermaid
---
config:
    layout: elk
---
erDiagram
    users ||..o{ attendances: "have"
    attendances ||..o{ break_times: "contain"
    attendances ||..o{ attnd-corrections: "have"
    attnd-corrections ||..o{ break-corrections: "contain"
    break_times |o..o{ break-corrections: "have"

    users {
        unsignedBigInt id PK
        varchar(255) name
        varchar(255) email UK
        datetime email_verified_at "nullable"
        tinyint(1) is_admin "default false"
        varchar(255) password
        datetime created_at "nullable"
        datetime updated_at "nullable"
    }

    attendances {
        unsignedBigInt id PK
        unsignedBigInt user_id FK, UK "unique([user_id, date])"
        date date UK "unique([user_id, date])"
        datetime clocked_in_at
        datetime clocked_out_at "nullable"
        datetime created_at "nullable"
        datetime updated_at "nullable"
    }

    break_times {
        unsignedBigInt id PK
        unsignedBigInt attendance_id FK "index"
        datetime started_at
        datetime ended_at "nullable"
        datetime created_at "nullable"
        datetime updated_at "nullable"
    }

    attnd-corrections["attendance_correction_applications"] {
        unsignedBigInt id PK
        unsignedBigInt attendance_id FK
        unsignedTinyInt status "0:pending 1:approved / default 0"
        datetime new_clocked_in_at
        datetime new_clocked_out_at
        datetime created_at "nullable"
        datetime updated_at "nullable"
    }

    break-corrections["break_correction_applications"] {
        unsignedBigInt id PK
        unsignedBigInt attendance_correction_application_id FK
        unsignedBigInt break_time_id FK "nullable"
        datetime new_started_at
        datetime new_ended_at
        datetime created_at "nullable"
        datetime updated_at "nullable"
    }
```
