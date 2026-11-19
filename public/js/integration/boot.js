/* Integrasi detail — boot. Data server: window.BT (lihat integration-detail.blade.php). */
// Initial Boot
document.addEventListener('DOMContentLoaded', function() {
    renderDecisionTree();
    renderFaqRulesList();
    renderSocialChannelsList();
    updateSubTabToggleStates();

    // Check active tab from URL query params or server session
    const urlParams = new URLSearchParams(window.location.search);
    const initialTab = urlParams.get('tab') || BT.initialTab;
    if (initialTab && initialTab !== 'bot') {
        switchDetailTab(initialTab);
    } else {
        initAdminMascotSandbox();
    }
});
