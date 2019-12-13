export const stubFetchingUsers = cy => {
    cy.fixture('users.json').as('users');
    cy.route('GET', '/api/users', '@users');
};
