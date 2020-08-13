export const stubGuest = cy => {
    cy.fixture('xhr-data/users/guest.json').as('guestUser');
    cy.route({
        method: 'GET',
        url: '/api/users/@me',
        delay: 1000,
        response: '@guestUser'
    }).as('loadGuestUser');
};
