describe('Dashboard Screen for Admin user', function () {
    it('detects heading "Account Status"', function () {
        cy.server();

        cy.fixture('users/admin.json').as('authenticatedAdminUser');
        cy.route('GET', '/api/users/@me', '@authenticatedAdminUser');

        cy.fixture('groups.json').as('groups');
        cy.route('GET', '/api/groups', '@groups');
        cy.route('GET', '/api/users/@me/characters', []);

        cy.fixture('.sets.json').as('sets');
        cy.route('GET', '/api/sets', '@sets');

        cy.fixture('.skills.json').as('skills');
        cy.route('GET', '/api/skills', '@skills');

        cy.visit('/');
        cy.get('h2').should('have.text', 'Account Status');
    });
});
