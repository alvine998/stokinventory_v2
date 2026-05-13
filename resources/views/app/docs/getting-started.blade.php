@extends('layouts.app', ['title' => 'Getting Started — Docs', 'heading' => 'Documentation'])

@section('content')
<div class="docs-layout">
    @include('app.docs._sidebar')

    <div class="docs-content">
        <div class="docs-hero">
            <p class="eyebrow">Documentation › Getting Started</p>
            <h2><i class="fa-solid fa-rocket"></i> Getting Started</h2>
            <p>Everything you need to know to set up your account, invite your team, and start managing inventory.</p>
        </div>

        {{-- Registration --}}
        <div class="docs-section" id="registration">
            <div class="docs-section-title"><i class="fa-solid fa-user-plus"></i> Account Registration</div>
            <div class="docs-section-subtitle">Create a new business account on StokInventory.</div>
            <div class="docs-req">
                <span class="docs-req-pill"><i class="fa-solid fa-circle-check"></i> No account required</span>
                <span class="docs-req-pill warn"><i class="fa-solid fa-envelope"></i> Valid email address</span>
            </div>
            <div class="docs-steps">
                <div class="docs-step"><div class="docs-step-num">1</div><div class="docs-step-body"><strong>Go to the registration page</strong><p>Navigate to <code>/register</code> or click "Register" on the login page.</p></div></div>
                <div class="docs-step"><div class="docs-step-num">2</div><div class="docs-step-body"><strong>Fill in your details</strong><p>Enter your full name, business name, email, and a secure password (minimum 8 characters).</p></div></div>
                <div class="docs-step"><div class="docs-step-num">3</div><div class="docs-step-body"><strong>Submit the form</strong><p>Click "Create Account". You will be redirected to the onboarding wizard.</p></div></div>
                <div class="docs-step"><div class="docs-step-num">4</div><div class="docs-step-body"><strong>Complete onboarding</strong><p>Set your business type, timezone, and currency preference. This configures your workspace defaults.</p></div></div>
            </div>
            <div class="docs-tip"><i class="fa-solid fa-lightbulb"></i> <strong>Owner role:</strong> The first user of a business is automatically assigned the <em>Owner</em> role, which grants access to all features.</div>
        </div>

        {{-- Login --}}
        <div class="docs-section" id="login">
            <div class="docs-section-title"><i class="fa-solid fa-right-to-bracket"></i> Logging In</div>
            <div class="docs-section-subtitle">Access your inventory workspace securely.</div>
            <div class="docs-steps">
                <div class="docs-step"><div class="docs-step-num">1</div><div class="docs-step-body"><strong>Go to <code>/login</code></strong><p>Enter the email and password you registered with.</p></div></div>
                <div class="docs-step"><div class="docs-step-num">2</div><div class="docs-step-body"><strong>Click "Sign In"</strong><p>On success, you are redirected to the Dashboard. Failed logins are recorded in Login History.</p></div></div>
                <div class="docs-step"><div class="docs-step-num">3</div><div class="docs-step-body"><strong>To log out</strong><p>Click the logout icon (→) in the top-right corner of the application.</p></div></div>
            </div>
        </div>

        {{-- Permissions --}}
        <div class="docs-section" id="permissions">
            <div class="docs-section-title"><i class="fa-solid fa-shield-halved"></i> Understanding Permissions</div>
            <div class="docs-section-subtitle">Access to features is controlled by roles. Each role holds a set of permission keys.</div>
            <div class="docs-steps">
                <div class="docs-step"><div class="docs-step-num">1</div><div class="docs-step-body"><strong>Permission groups</strong><p>Permissions are grouped by module: <em>workspace, people, inventory, master data, inventory ops, purchasing, sales, finance, reporting, team access</em>.</p></div></div>
                <div class="docs-step"><div class="docs-step-num">2</div><div class="docs-step-body"><strong>Owner / Super Admin</strong><p>Users with the <em>Owner</em> role slug or the <code>is_super_admin</code> flag automatically pass every permission check — no configuration needed.</p></div></div>
                <div class="docs-step"><div class="docs-step-num">3</div><div class="docs-step-body"><strong>Creating custom roles</strong><p>Go to <a href="{{ route('roles.index') }}">Roles</a>, click "New Role", enter a name, and tick the permission groups you want to grant.</p></div></div>
                <div class="docs-step"><div class="docs-step-num">4</div><div class="docs-step-body"><strong>Assigning roles to users</strong><p>Go to <a href="{{ route('user-roles.index') }}">User Roles</a>, select a user and the role to assign.</p></div></div>
            </div>
            <div class="docs-tip"><i class="fa-solid fa-lightbulb"></i> A user can hold multiple roles. Their effective permissions are the union of all roles they are assigned to.</div>
        </div>

        {{-- Users --}}
        <div class="docs-section" id="users">
            <div class="docs-section-title"><i class="fa-solid fa-users"></i> Inviting Team Members</div>
            <div class="docs-section-subtitle">Add staff accounts so your team can work in the same workspace.</div>
            <div class="docs-req">
                <span class="docs-req-pill"><i class="fa-solid fa-key"></i> Permission: users.manage</span>
            </div>
            <div class="docs-steps">
                <div class="docs-step"><div class="docs-step-num">1</div><div class="docs-step-body"><strong>Go to <a href="{{ route('users.index') }}">Users</a></strong><p>Click "Add User" to open the creation form.</p></div></div>
                <div class="docs-step"><div class="docs-step-num">2</div><div class="docs-step-body"><strong>Fill in name, email, and password</strong><p>The user can log in immediately with these credentials.</p></div></div>
                <div class="docs-step"><div class="docs-step-num">3</div><div class="docs-step-body"><strong>Assign a role</strong><p>After saving, go to <a href="{{ route('user-roles.index') }}">User Roles</a> and assign the appropriate role to control what the user can access.</p></div></div>
            </div>
        </div>

        {{-- Company Profile --}}
        <div class="docs-section" id="company">
            <div class="docs-section-title"><i class="fa-solid fa-building"></i> Company Profile</div>
            <div class="docs-section-subtitle">Configure your business identity used across invoices and documents.</div>
            <div class="docs-req">
                <span class="docs-req-pill"><i class="fa-solid fa-key"></i> Permission: company.manage</span>
            </div>
            <div class="docs-steps">
                <div class="docs-step"><div class="docs-step-num">1</div><div class="docs-step-body"><strong>Go to <a href="{{ route('company.edit') }}">Company Settings</a></strong><p>Found under the Business section in the sidebar.</p></div></div>
                <div class="docs-step"><div class="docs-step-num">2</div><div class="docs-step-body"><strong>Fill in all fields</strong><p>Company name, address, phone, tax ID (NPWP), logo. These appear on printed invoices and delivery orders.</p></div></div>
                <div class="docs-step"><div class="docs-step-num">3</div><div class="docs-step-body"><strong>Save changes</strong><p>Click "Save". Changes take effect immediately across the application.</p></div></div>
            </div>
        </div>
    </div>
</div>
@endsection
