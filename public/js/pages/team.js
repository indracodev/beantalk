/* team — dipindah dari view Blade agar bisa di-cache. */
function openEditModal(member) {
    const form = document.getElementById('formEditTeamMember');
    form.action = '/admin/team/' + member.id;
    document.getElementById('editStaffName').value = member.name || '';
    document.getElementById('editStaffUsername').value = member.username || '';
    document.getElementById('editStaffEmail').value = member.email || '';
    document.getElementById('editStaffTelegram').value = member.telegram_username || '';
    document.getElementById('editStaffPassword').value = '';
    document.getElementById('editStaffRole').value = member.role || 'agent';
    openModal('modalEditTeamMember');
}
