import { stubFetchingGroups } from "../fixtures/helpers/groups";
import { stubFetchingSets } from "../fixtures/helpers/sets";
import { stubFetchingSkills } from "../fixtures/helpers/skills";
import { stubFetchingTeams } from "../fixtures/helpers/teams";
import { stubFetchingAdminUser } from "../fixtures/helpers/users/admin-user";

describe('Dashboard Screen for Admin user', function () {
    it('detects heading "Account Status"', function () {
        cy.server();

        cy.fixture('users/admin.json').as('adminUser');
        cy.route('GET', '/api/users/@me', '@adminUser');

        stubFetchingGroups(cy);
        stubFetchingAdminUser(cy);
        stubFetchingSets(cy);
        stubFetchingSkills(cy);
        stubFetchingTeams(cy);
        cy.route('GET', '/api/users/@me/characters', []);

        cy.visit('/');
        cy.get('h2').should('have.text', 'Account Status');
    });
});
