export const stubGuest = cy => {
    cy.fixture('xhr-data/users/guest.json').as('guestUser');
    cy.route('GET', '/api/users/@me', '@guestUser');
};
