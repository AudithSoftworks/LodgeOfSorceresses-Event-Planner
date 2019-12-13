export const stubSoulshrivenUserAfterNameChange = cy => {
    cy.fixture('users/soulshriven-after-name-change.json').as('authenticatedSoulshrivenUserAfterNameChange');
    cy.route('GET', '/api/users/@me', '@authenticatedSoulshrivenUserAfterNameChange');
};
