export const users = cy => {
    cy.fixture('users.json').as('users');
    cy.route({
        method: 'GET',
        url: '/api/users',
        response: '@users'
    }).as('loadUsers');
};
