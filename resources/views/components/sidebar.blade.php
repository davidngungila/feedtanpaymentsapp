<!-- Menu -->
<aside id="layout-menu" class="layout-menu menu-vertical menu bg-menu-theme">
    <div class="app-brand demo">
        <a href="{{ route('dashboard') }}" class="app-brand-link">
          
            <span class="app-brand-text demo menu-text fw-bold ms-2">FeedTan APP</span>
        </a>

        <a href="javascript:void(0);" class="layout-menu-toggle menu-link text-large ms-auto">
            <i class="bx bx-chevron-left d-block d-xl-none align-middle"></i>
        </a>
    </div>

    <div class="menu-divider mt-0"></div>

    <div class="menu-inner-shadow"></div>

    <ul class="menu-inner py-1">
        <!-- Dashboard -->
        <li class="menu-item {{ request()->routeIs('dashboard') ? 'active' : '' }}">
            <a href="{{ route('dashboard') }}" class="menu-link">
                <i class="menu-icon tf-icons bx bx-home-smile"></i>
                <div class="text-truncate" data-i18n="Dashboard">Dashboard</div>
            </a>
        </li>

        <!-- ClickPesa -->
        <li class="menu-item {{ request()->routeIs('payments*') ? 'active open' : '' }} {{ request()->routeIs('report*') ? 'active open' : '' }}">
            <a href="javascript:void(0);" class="menu-link menu-toggle">
                <i class="menu-icon tf-icons bx bx-credit-card"></i>
                <div class="text-truncate" data-i18n="ClickPesa">ClickPesa</div>
            </a>
            <ul class="menu-sub">
                                <li class="menu-item {{ request()->routeIs('payments.initiate') ? 'active' : '' }}">
                    <a href="{{ route('payments.initiate') }}" class="menu-link">
                        <div class="text-truncate" data-i18n="Initiate Payment">Initiate Payment</div>
                    </a>
                </li>
                <li class="menu-item {{ request()->routeIs('payments.history') ? 'active' : '' }}">
                    <a href="{{ route('payments.history') }}" class="menu-link">
                        <div class="text-truncate" data-i18n="Payment History">Payment History</div>
                    </a>
                </li>
                <li class="menu-item {{ request()->routeIs('billpay.all') ? 'active' : '' }}">
                    <a href="{{ route('billpay.all') }}" class="menu-link">
                        <div class="text-truncate" data-i18n="All Bills">All Bills</div>
                    </a>
                </li>
                <li class="menu-item {{ request()->routeIs('billpay.create') ? 'active' : '' }}">
                    <a href="{{ route('billpay.create') }}" class="menu-link">
                        <div class="text-truncate" data-i18n="Create Bill">Create Bill</div>
                    </a>
                </li>
                <li class="menu-item {{ request()->routeIs('report.statement') ? 'active' : '' }}">
                    <a href="{{ route('report.statement') }}" class="menu-link">
                        <div class="text-truncate" data-i18n="Statement">Statement</div>
                    </a>
                </li>
            </ul>
        </li>

        <!-- Messaging -->
        <li class="menu-item {{ request()->routeIs('messaging*') ? 'active open' : '' }}">
            <a href="javascript:void(0);" class="menu-link menu-toggle">
                <i class="menu-icon tf-icons bx bx-message-square-dots"></i>
                <div class="text-truncate" data-i18n="Messaging">Messaging</div>
            </a>
            <ul class="menu-sub">
                <li class="menu-item {{ request()->routeIs('messaging.dashboard') ? 'active' : '' }}">
                    <a href="{{ route('messaging.dashboard') }}" class="menu-link">
                        <div class="text-truncate" data-i18n="Dashboard">Dashboard</div>
                    </a>
                </li>
                <li class="menu-item {{ request()->routeIs('messaging.sms') ? 'active' : '' }}">
                    <a href="{{ route('messaging.sms') }}" class="menu-link">
                        <div class="text-truncate" data-i18n">Send SMS</div>
                    </a>
                </li>
                <li class="menu-item {{ request()->routeIs('messaging.email') ? 'active' : '' }}">
                    <a href="{{ route('messaging.email') }}" class="menu-link">
                        <div class="text-truncate" data-i18n">Send Email</div>
                    </a>
                </li>
                <li class="menu-item {{ request()->routeIs('messaging.services') ? 'active' : '' }}">
                    <a href="{{ route('messaging.services') }}" class="menu-link">
                        <div class="text-truncate" data-i18n">Services</div>
                    </a>
                </li>
            </ul>
        </li>

        <!-- Members -->
        <li class="menu-item {{ request()->routeIs('members*') ? 'active open' : '' }}">
            <a href="javascript:void(0);" class="menu-link menu-toggle">
                <i class="menu-icon tf-icons bx bx-group"></i>
                <div class="text-truncate" data-i18n="Members">Members</div>
            </a>
            <ul class="menu-sub">
                <li class="menu-item {{ request()->routeIs('members.all') ? 'active' : '' }}">
                    <a href="{{ route('members.all') }}" class="menu-link">
                        <div class="text-truncate" data-i18n="All Members">All Members</div>
                    </a>
                </li>
                <li class="menu-item {{ request()->routeIs('members.add') ? 'active' : '' }}">
                    <a href="{{ route('members.add') }}" class="menu-link">
                        <div class="text-truncate" data-i18n="Add Member">Add Member</div>
                    </a>
                </li>
                <li class="menu-item {{ request()->routeIs('members.profiles') ? 'active' : '' }}">
                    <a href="{{ route('members.profiles') }}" class="menu-link">
                        <div class="text-truncate" data-i18n="Member Profiles">Member Profiles</div>
                    </a>
                </li>
                <li class="menu-item {{ request()->routeIs('members.groups') ? 'active' : '' }}">
                    <a href="{{ route('members.groups') }}" class="menu-link">
                        <div class="text-truncate" data-i18n="Member Groups">Member Groups</div>
                    </a>
                </li>
                <li class="menu-item {{ request()->routeIs('members.contributions') ? 'active' : '' }}">
                    <a href="{{ route('members.contributions') }}" class="menu-link">
                        <div class="text-truncate" data-i18n="Contributions">Contributions</div>
                    </a>
                </li>
                <li class="menu-item {{ request()->routeIs('members.reports') ? 'active' : '' }}">
                    <a href="{{ route('members.reports') }}" class="menu-link">
                        <div class="text-truncate" data-i18n="Member Reports">Member Reports</div>
                    </a>
                </li>
            </ul>
        </li>

        <!-- Investment -->
        <li class="menu-item {{ request()->routeIs('investment*') ? 'active open' : '' }}">
            <a href="javascript:void(0);" class="menu-link menu-toggle">
                <i class="menu-icon tf-icons bx bx-trending-up"></i>
                <div class="text-truncate" data-i18n="Investment">Investment</div>
            </a>
            <ul class="menu-sub">
                <li class="menu-item {{ request()->routeIs('investment.view') ? 'active' : '' }}">
                    <a href="{{ route('investment.view') }}" class="menu-link">
                        <div class="text-truncate" data-i18n="View Investments">View Investments</div>
                    </a>
                </li>
                <li class="menu-item {{ request()->routeIs('investment.new') ? 'active' : '' }}">
                    <a href="{{ route('investment.new') }}" class="menu-link">
                        <div class="text-truncate" data-i18n="New Investment">New Investment</div>
                    </a>
                </li>
                <li class="menu-item {{ request()->routeIs('investment.plans') ? 'active' : '' }}">
                    <a href="{{ route('investment.plans') }}" class="menu-link">
                        <div class="text-truncate" data-i18n="Investment Plans">Investment Plans</div>
                    </a>
                </li>
                <li class="menu-item {{ request()->routeIs('investment.returns') ? 'active' : '' }}">
                    <a href="{{ route('investment.returns') }}" class="menu-link">
                        <div class="text-truncate" data-i18n="Returns / Profits">Returns / Profits</div>
                    </a>
                </li>
                <li class="menu-item {{ request()->routeIs('investment.history') ? 'active' : '' }}">
                    <a href="{{ route('investment.history') }}" class="menu-link">
                        <div class="text-truncate" data-i18n="Investment History">Investment History</div>
                    </a>
                </li>
                <li class="menu-item {{ request()->routeIs('investment.reports') ? 'active' : '' }}">
                    <a href="{{ route('investment.reports') }}" class="menu-link">
                        <div class="text-truncate" data-i18n="Reports">Reports</div>
                    </a>
                </li>
            </ul>
        </li>

        <!-- Deposit / Savings -->
        <li class="menu-item {{ request()->routeIs('savings*') ? 'active open' : '' }}">
            <a href="javascript:void(0);" class="menu-link menu-toggle">
                <i class="menu-icon tf-icons bx bx-wallet"></i>
                <div class="text-truncate" data-i18n="Deposit / Savings">Deposit / Savings</div>
            </a>
            <ul class="menu-sub">
                <li class="menu-item {{ request()->routeIs('savings.deposit') ? 'active' : '' }}">
                    <a href="{{ route('savings.deposit') }}" class="menu-link">
                        <div class="text-truncate" data-i18n="Make Deposit">Make Deposit</div>
                    </a>
                </li>
                <li class="menu-item {{ request()->routeIs('savings.accounts') ? 'active' : '' }}">
                    <a href="{{ route('savings.accounts') }}" class="menu-link">
                        <div class="text-truncate" data-i18n="Savings Accounts">Savings Accounts</div>
                    </a>
                </li>
                <li class="menu-item {{ request()->routeIs('savings.history') ? 'active' : '' }}">
                    <a href="{{ route('savings.history') }}" class="menu-link">
                        <div class="text-truncate" data-i18n="Transaction History">Transaction History</div>
                    </a>
                </li>
                <li class="menu-item {{ request()->routeIs('savings.withdrawal') ? 'active' : '' }}">
                    <a href="{{ route('savings.withdrawal') }}" class="menu-link">
                        <div class="text-truncate" data-i18n="Withdrawal Requests">Withdrawal Requests</div>
                    </a>
                </li>
                <li class="menu-item {{ request()->routeIs('savings.reports') ? 'active' : '' }}">
                    <a href="{{ route('savings.reports') }}" class="menu-link">
                        <div class="text-truncate" data-i18n="Savings Reports">Savings Reports</div>
                    </a>
                </li>
            </ul>
        </li>

        <!-- Loans -->
        <li class="menu-item {{ request()->routeIs('loans*') ? 'active open' : '' }}">
            <a href="javascript:void(0);" class="menu-link menu-toggle">
                <i class="menu-icon tf-icons bx bx-dollar"></i>
                <div class="text-truncate" data-i18n="Loans">Loans</div>
            </a>
            <ul class="menu-sub">
                <li class="menu-item {{ request()->routeIs('loans.apply') ? 'active' : '' }}">
                    <a href="{{ route('loans.apply') }}" class="menu-link">
                        <div class="text-truncate" data-i18n="Apply Loan">Apply Loan</div>
                    </a>
                </li>
                <li class="menu-item {{ request()->routeIs('loans.products') ? 'active' : '' }}">
                    <a href="{{ route('loans.products') }}" class="menu-link">
                        <div class="text-truncate" data-i18n="Loan Products">Loan Products</div>
                    </a>
                </li>
                <li class="menu-item {{ request()->routeIs('loans.my') ? 'active' : '' }}">
                    <a href="{{ route('loans.my') }}" class="menu-link">
                        <div class="text-truncate" data-i18n="My Loans">My Loans</div>
                    </a>
                </li>
                <li class="menu-item {{ request()->routeIs('loans.repayments') ? 'active' : '' }}">
                    <a href="{{ route('loans.repayments') }}" class="menu-link">
                        <div class="text-truncate" data-i18n="Repayments">Repayments</div>
                    </a>
                </li>
                <li class="menu-item {{ request()->routeIs('loans.schedule') ? 'active' : '' }}">
                    <a href="{{ route('loans.schedule') }}" class="menu-link">
                        <div class="text-truncate" data-i18n="Loan Schedule">Loan Schedule</div>
                    </a>
                </li>
                <li class="menu-item {{ request()->routeIs('loans.reports') ? 'active' : '' }}">
                    <a href="{{ route('loans.reports') }}" class="menu-link">
                        <div class="text-truncate" data-i18n="Loan Reports">Loan Reports</div>
                    </a>
                </li>
            </ul>
        </li>

        <!-- Social Welfare Fund -->
        <li class="menu-item {{ request()->routeIs('welfare*') ? 'active open' : '' }}">
            <a href="javascript:void(0);" class="menu-link menu-toggle">
                <i class="menu-icon tf-icons bx bx-heart"></i>
                <div class="text-truncate" data-i18n="Social Welfare Fund">Social Welfare Fund</div>
            </a>
            <ul class="menu-sub">
                <li class="menu-item {{ request()->routeIs('welfare.contribute') ? 'active' : '' }}">
                    <a href="{{ route('welfare.contribute') }}" class="menu-link">
                        <div class="text-truncate" data-i18n="Contribute">Contribute</div>
                    </a>
                </li>
                <li class="menu-item {{ request()->routeIs('welfare.balance') ? 'active' : '' }}">
                    <a href="{{ route('welfare.balance') }}" class="menu-link">
                        <div class="text-truncate" data-i18n="Fund Balance">Fund Balance</div>
                    </a>
                </li>
                <li class="menu-item {{ request()->routeIs('welfare.support') ? 'active' : '' }}">
                    <a href="{{ route('welfare.support') }}" class="menu-link">
                        <div class="text-truncate" data-i18n="Request Support">Request Support</div>
                    </a>
                </li>
                <li class="menu-item {{ request()->routeIs('welfare.history') ? 'active' : '' }}">
                    <a href="{{ route('welfare.history') }}" class="menu-link">
                        <div class="text-truncate" data-i18n="Contributions History">Contributions History</div>
                    </a>
                </li>
                <li class="menu-item {{ request()->routeIs('welfare.reports') ? 'active' : '' }}">
                    <a href="{{ route('welfare.reports') }}" class="menu-link">
                        <div class="text-truncate" data-i18n="Welfare Reports">Welfare Reports</div>
                    </a>
                </li>
            </ul>
        </li>

        <!-- Shares -->
        <li class="menu-item {{ request()->routeIs('shares*') ? 'active open' : '' }}">
            <a href="javascript:void(0);" class="menu-link menu-toggle">
                <i class="menu-icon tf-icons bx bx-pie-chart"></i>
                <div class="text-truncate" data-i18n="Shares">Shares</div>
            </a>
            <ul class="menu-sub">
                <li class="menu-item {{ request()->routeIs('shares.buy') ? 'active' : '' }}">
                    <a href="{{ route('shares.buy') }}" class="menu-link">
                        <div class="text-truncate" data-i18n="Buy Shares">Buy Shares</div>
                    </a>
                </li>
                <li class="menu-item {{ request()->routeIs('shares.my') ? 'active' : '' }}">
                    <a href="{{ route('shares.my') }}" class="menu-link">
                        <div class="text-truncate" data-i18n="My Shares">My Shares</div>
                    </a>
                </li>
                <li class="menu-item {{ request()->routeIs('shares.value') ? 'active' : '' }}">
                    <a href="{{ route('shares.value') }}" class="menu-link">
                        <div class="text-truncate" data-i18n="Share Value">Share Value</div>
                    </a>
                </li>
                <li class="menu-item {{ request()->routeIs('shares.dividends') ? 'active' : '' }}">
                    <a href="{{ route('shares.dividends') }}" class="menu-link">
                        <div class="text-truncate" data-i18n="Dividends">Dividends</div>
                    </a>
                </li>
                <li class="menu-item {{ request()->routeIs('shares.transfers') ? 'active' : '' }}">
                    <a href="{{ route('shares.transfers') }}" class="menu-link">
                        <div class="text-truncate" data-i18n="Share Transfers">Share Transfers</div>
                    </a>
                </li>
                <li class="menu-item {{ request()->routeIs('shares.reports') ? 'active' : '' }}">
                    <a href="{{ route('shares.reports') }}" class="menu-link">
                        <div class="text-truncate" data-i18n="Share Reports">Share Reports</div>
                    </a>
                </li>
            </ul>
        </li>

            <!-- Account Settings -->
      
        <li class="menu-item {{ request()->routeIs('account-settings*') ? 'active open' : '' }}">
            <a href="javascript:void(0);" class="menu-link menu-toggle">
                <i class="menu-icon tf-icons bx bx-dock-top"></i>
                <div class="text-truncate" data-i18n="Account Settings">Account Settings</div>
            </a>
            <ul class="menu-sub">
                <li class="menu-item {{ request()->routeIs('profile') ? 'active' : '' }}">
                    <a href="{{ route('profile') }}" class="menu-link">
                        <div class="text-truncate" data-i18n="My Profile">My Profile</div>
                    </a>
                </li>
                <li class="menu-item {{ request()->routeIs('account-settings.notifications') ? 'active' : '' }}">
                    <a href="{{ route('account-settings.notifications') }}" class="menu-link">
                        <div class="text-truncate" data-i18n="Notifications">Notifications</div>
                    </a>
                </li>
                <li class="menu-item {{ request()->routeIs('security') ? 'active' : '' }}">
                    <a href="{{ route('security') }}" class="menu-link">
                        <div class="text-truncate" data-i18n="Security">Security</div>
                    </a>
                </li>
            </ul>
        </li>

        </li>

        <!-- System Settings -->
        
        <li class="menu-item {{ request()->routeIs('system-settings*') ? 'active open' : '' }}">
            <a href="javascript:void(0);" class="menu-link menu-toggle">
                <i class="menu-icon tf-icons bx bx-cog"></i>
                <div class="text-truncate" data-i18n="System Settings">System Settings</div>
            </a>
            <ul class="menu-sub">
                <li class="menu-item {{ request()->routeIs('system-settings.general') ? 'active' : '' }}">
                    <a href="{{ route('system-settings.general') }}" class="menu-link">
                        <div class="text-truncate" data-i18n="General Settings">General Settings</div>
                    </a>
                </li>
                <li class="menu-item {{ request()->routeIs('system-settings.payment') ? 'active' : '' }}">
                    <a href="{{ route('system-settings.payment') }}" class="menu-link">
                        <div class="text-truncate" data-i18n="Payment Settings">Payment Settings</div>
                    </a>
                </li>
                <li class="menu-item {{ request()->routeIs('system-settings.security') ? 'active' : '' }}">
                    <a href="{{ route('system-settings.security') }}" class="menu-link">
                        <div class="text-truncate" data-i18n="Security Settings">Security Settings</div>
                    </a>
                </li>
                <li class="menu-item {{ request()->routeIs('system-settings.notification') ? 'active' : '' }}">
                    <a href="{{ route('system-settings.notification') }}" class="menu-link">
                        <div class="text-truncate" data-i18n="Notification Settings">Notification Settings</div>
                    </a>
                </li>
                <li class="menu-item {{ request()->routeIs('system-settings.user') ? 'active' : '' }}">
                    <a href="{{ route('system-settings.user') }}" class="menu-link">
                        <div class="text-truncate" data-i18n="User Settings">User Settings</div>
                    </a>
                </li>
                <li class="menu-item {{ request()->routeIs('system-settings.integration') ? 'active' : '' }}">
                    <a href="{{ route('system-settings.integration') }}" class="menu-link">
                        <div class="text-truncate" data-i18n="Integration Settings">Integration Settings</div>
                    </a>
                </li>
                <li class="menu-item {{ request()->routeIs('system-settings.maintenance') ? 'active' : '' }}">
                    <a href="{{ route('system-settings.maintenance') }}" class="menu-link">
                        <div class="text-truncate" data-i18n="System Maintenance">System Maintenance</div>
                    </a>
                </li>
                <li class="menu-item {{ request()->routeIs('system-settings.health') ? 'active' : '' }}">
                    <a href="{{ route('system-settings.health') }}" class="menu-link">
                        <div class="text-truncate" data-i18n="System Health">System Health</div>
                    </a>
                </li>
                <li class="menu-item {{ request()->routeIs('system-settings.audit') ? 'active' : '' }}">
                    <a href="{{ route('system-settings.audit') }}" class="menu-link">
                        <div class="text-truncate" data-i18n="Audit Trail">Audit Trail</div>
                    </a>
                </li>
                <li class="menu-item {{ request()->routeIs('system-settings.security*') ? 'active open' : '' }}">
                    <a href="javascript:void(0);" class="menu-link menu-toggle">
                        <div class="text-truncate" data-i18n="Security Center">Security Center</div>
                    </a>
                    <ul class="menu-sub">
                        <li class="menu-item {{ request()->routeIs('system-settings.security.authentication') ? 'active' : '' }}">
                            <a href="{{ route('system-settings.security.authentication') }}" class="menu-link">
                                <div class="text-truncate" data-i18n="Advanced Authentication">Advanced Authentication</div>
                            </a>
                        </li>
                        <li class="menu-item {{ request()->routeIs('system-settings.security.fraud') ? 'active' : '' }}">
                            <a href="{{ route('system-settings.security.fraud') }}" class="menu-link">
                                <div class="text-truncate" data-i18n="Fraud Detection">Fraud Detection</div>
                            </a>
                        </li>
                        <li class="menu-item {{ request()->routeIs('system-settings.security.access') ? 'active' : '' }}">
                            <a href="{{ route('system-settings.security.access') }}" class="menu-link">
                                <div class="text-truncate" data-i18n="Access Control">Access Control</div>
                            </a>
                        </li>
                        <li class="menu-item {{ request()->routeIs('system-settings.security.device') ? 'active' : '' }}">
                            <a href="{{ route('system-settings.security.device') }}" class="menu-link">
                                <div class="text-truncate" data-i18n="IP & Device Security">IP & Device Security</div>
                            </a>
                        </li>
                        <li class="menu-item {{ request()->routeIs('system-settings.security.session') ? 'active' : '' }}">
                            <a href="{{ route('system-settings.security.session') }}" class="menu-link">
                                <div class="text-truncate" data-i18n="Session Security">Session Security</div>
                            </a>
                        </li>
                        <li class="menu-item {{ request()->routeIs('system-settings.security.protection') ? 'active' : '' }}">
                            <a href="{{ route('system-settings.security.protection') }}" class="menu-link">
                                <div class="text-truncate" data-i18n="System Protection">System Protection</div>
                            </a>
                        </li>
                        <li class="menu-item {{ request()->routeIs('system-settings.security.alerts') ? 'active' : '' }}">
                            <a href="{{ route('system-settings.security.alerts') }}" class="menu-link">
                                <div class="text-truncate" data-i18n="Security Alerts">Security Alerts</div>
                            </a>
                        </li>
                        <li class="menu-item {{ request()->routeIs('system-settings.security.tracking') ? 'active' : '' }}">
                            <a href="{{ route('system-settings.security.tracking') }}" class="menu-link">
                                <div class="text-truncate" data-i18n="Activity Tracking">Activity Tracking</div>
                            </a>
                        </li>
                    </ul>
                </li>
            </ul>
        </li>

        </ul>
</aside>
<!-- / Menu -->
