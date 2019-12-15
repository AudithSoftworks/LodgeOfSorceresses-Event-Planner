export const stubSoulshrivenUserBeforeNameChange = cy => {
    cy.fixture('users/soulshriven-before-name-change.json').as('authenticatedSoulshrivenUserBeforeNameChange');
    cy.route('GET', '/api/users/@me', '@authenticatedSoulshrivenUserBeforeNameChange');
};
