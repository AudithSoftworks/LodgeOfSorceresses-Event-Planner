export const stubFetchingAdminUser = cy => {
    cy.fixture('users/admin.json').as('admin');
    cy.route('GET', '/api/users/@me', '@admin');
};
