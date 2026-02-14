@extends('layouts.app')

@section('title', 'Dashboard')

@section('content')
<div class="row gx-5 gx-xl-10">
  <div class="col-xl-12 mb-5 mb-xl-10">
    @include('pages.dashboard.widgets.relatorySalles')
  </div>
  <div class="col-xxl-4 mb-5 mb-xl-10">
    <div class="card card-flush h-xl-100">
      <div class="card-header py-7">
        <h3 class="card-title align-items-start flex-column">
          <span class="card-label fw-bold text-gray-800"
            >Últimos MiCores</span
          >
          <span class="text-gray-500 mt-1 fw-semibold fs-6">
            {{ $latestMiCores->count() }} recentes
          </span>
        </h3>
      </div>

      <div class="card-body d-flex justify-content-between flex-column pt-3">
        @forelse ($latestMiCores as $miCore)
          @php($domain = optional($miCore->domains->first())->domain)
          <div class="d-flex flex-stack">
            <div class="symbol symbol-30px me-4">
              <span class="symbol-label bg-light-primary">
                <i class="ki-outline ki-abstract-26 fs-3 text-primary"></i>
              </span>
            </div>

            <div
              class="d-flex align-items-center flex-stack flex-wrap flex-row-fluid d-grid gap-2"
            >
              <div class="me-5">
                <a
                  href="{{ route('clients.show', $miCore->id) }}"
                  class="text-gray-800 fw-bold text-hover-primary fs-6"
                >
                  {{ $miCore->name }}
                </a>

                <span
                  class="text-gray-500 fw-semibold fs-7 d-block text-start ps-0"
                >
                  {{ $domain ?: 'Sem domínio' }}
                </span>
              </div>

              <div class="d-flex align-items-center">
                <span class="badge badge-light-primary fs-8">
                  {{ $miCore->created_at->format('d/m/Y') }}
                </span>
              </div>
            </div>
          </div>

          @if (! $loop->last)
            <div class="separator separator-dashed my-3"></div>
          @endif
        @empty
          <div class="text-gray-500 fw-semibold fs-6">Nenhum MiCore encontrado.</div>
        @endforelse
      </div>
    </div>
  </div>

  <div class="col-xxl-4 mb-5 mb-xl-10">
    <div class="card card-flush h-xl-100">
      <div class="card-header py-7">
        <div class="m-0">
          <div class="d-flex align-items-center mb-2">
            <span class="fs-2hx fw-bold text-gray-800 me-2 lh-1 ls-n2"
              >5,037</span
            >

            <span class="badge badge-light-success fs-base">
              <i class="ki-outline ki-arrow-up fs-5 text-success ms-n1"></i>
              2.2%
            </span>
          </div>

          <span class="fs-6 fw-semibold text-gray-500"
            >Visits by Social Networks</span
          >
        </div>
      </div>

      <div
        class="card-body card-body d-flex justify-content-between flex-column pt-3"
      >
        <div class="d-flex flex-stack">
          <img
            src="/metronic8/demo32/assets/media/svg/brand-logos/dribbble-icon-1.svg"
            class="me-4 w-30px"
            style="border-radius: 4px"
            alt=""
          />

          <div
            class="d-flex align-items-center flex-stack flex-wrap flex-row-fluid d-grid gap-2"
          >
            <div class="me-5">
              <a href="#" class="text-gray-800 fw-bold text-hover-primary fs-6"
                >Dribbble</a
              >

              <span
                class="text-gray-500 fw-semibold fs-7 d-block text-start ps-0"
                >Community</span
              >
            </div>

            <div class="d-flex align-items-center">
              <span class="text-gray-800 fw-bold fs-4 me-3">579</span>

              <div class="m-0">
                <span class="badge badge-light-success fs-base">
                  <i class="ki-outline ki-arrow-up fs-5 text-success ms-n1"></i>
                  2.6%
                </span>
              </div>
            </div>
          </div>
        </div>

        <div class="separator separator-dashed my-3"></div>

        <div class="d-flex flex-stack">
          <img
            src="/metronic8/demo32/assets/media/svg/brand-logos/linkedin-1.svg"
            class="me-4 w-30px"
            style="border-radius: 4px"
            alt=""
          />

          <div
            class="d-flex align-items-center flex-stack flex-wrap flex-row-fluid d-grid gap-2"
          >
            <div class="me-5">
              <a href="#" class="text-gray-800 fw-bold text-hover-primary fs-6"
                >Linked In</a
              >

              <span
                class="text-gray-500 fw-semibold fs-7 d-block text-start ps-0"
                >Social Media</span
              >
            </div>

            <div class="d-flex align-items-center">
              <span class="text-gray-800 fw-bold fs-4 me-3">1,088</span>

              <div class="m-0">
                <span class="badge badge-light-danger fs-base">
                  <i
                    class="ki-outline ki-arrow-down fs-5 text-danger ms-n1"
                  ></i>
                  0.4%
                </span>
              </div>
            </div>
          </div>
        </div>

        <div class="separator separator-dashed my-3"></div>

        <div class="d-flex flex-stack">
          <img
            src="/metronic8/demo32/assets/media/svg/brand-logos/slack-icon.svg"
            class="me-4 w-30px"
            style="border-radius: 4px"
            alt=""
          />

          <div
            class="d-flex align-items-center flex-stack flex-wrap flex-row-fluid d-grid gap-2"
          >
            <div class="me-5">
              <a href="#" class="text-gray-800 fw-bold text-hover-primary fs-6"
                >Slack</a
              >

              <span
                class="text-gray-500 fw-semibold fs-7 d-block text-start ps-0"
                >Messanger</span
              >
            </div>

            <div class="d-flex align-items-center">
              <span class="text-gray-800 fw-bold fs-4 me-3">794</span>

              <div class="m-0">
                <span class="badge badge-light-success fs-base">
                  <i class="ki-outline ki-arrow-up fs-5 text-success ms-n1"></i>
                  0.2%
                </span>
              </div>
            </div>
          </div>
        </div>

        <div class="separator separator-dashed my-3"></div>

        <div class="d-flex flex-stack">
          <img
            src="/metronic8/demo32/assets/media/svg/brand-logos/youtube-3.svg"
            class="me-4 w-30px"
            style="border-radius: 4px"
            alt=""
          />

          <div
            class="d-flex align-items-center flex-stack flex-wrap flex-row-fluid d-grid gap-2"
          >
            <div class="me-5">
              <a href="#" class="text-gray-800 fw-bold text-hover-primary fs-6"
                >YouTube</a
              >

              <span
                class="text-gray-500 fw-semibold fs-7 d-block text-start ps-0"
                >Video Channel</span
              >
            </div>

            <div class="d-flex align-items-center">
              <span class="text-gray-800 fw-bold fs-4 me-3">978</span>

              <div class="m-0">
                <span class="badge badge-light-success fs-base">
                  <i class="ki-outline ki-arrow-up fs-5 text-success ms-n1"></i>
                  4.1%
                </span>
              </div>
            </div>
          </div>
        </div>

        <div class="separator separator-dashed my-3"></div>

        <div class="d-flex flex-stack">
          <img
            src="/metronic8/demo32/assets/media/svg/brand-logos/instagram-2-1.svg"
            class="me-4 w-30px"
            style="border-radius: 4px"
            alt=""
          />

          <div
            class="d-flex align-items-center flex-stack flex-wrap flex-row-fluid d-grid gap-2"
          >
            <div class="me-5">
              <a href="#" class="text-gray-800 fw-bold text-hover-primary fs-6"
                >Instagram</a
              >

              <span
                class="text-gray-500 fw-semibold fs-7 d-block text-start ps-0"
                >Social Network</span
              >
            </div>

            <div class="d-flex align-items-center">
              <span class="text-gray-800 fw-bold fs-4 me-3">1,458</span>

              <div class="m-0">
                <span class="badge badge-light-success fs-base">
                  <i class="ki-outline ki-arrow-up fs-5 text-success ms-n1"></i>
                  8.3%
                </span>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>

  <div class="col-xl-4 mb-5 mb-xl-0">
    <div class="card card-flush h-xl-100">
      <div class="card-header pt-7">
        <h3 class="card-title align-items-start flex-column">
          <span class="card-label fw-bold text-gray-800"
            >Módulos mais vendidos</span
          >
          <span class="text-gray-500 mt-1 fw-semibold fs-6">1.23k ativos</span>
        </h3>
      </div>

      <div class="card-body d-flex align-items-end">
        <div class="w-100">
          <div class="d-flex align-items-center">
            <div class="symbol symbol-30px me-5">
              <span class="symbol-label">
                <i class="ki-outline ki-rocket fs-3 text-gray-600"></i>
              </span>
            </div>

            <div
              class="d-flex align-items-center flex-stack flex-wrap d-grid gap-1 flex-row-fluid"
            >
              <div class="me-5">
                <a
                  href="#"
                  class="text-gray-800 fw-bold text-hover-primary fs-6"
                  >Direct Source</a
                >

                <span
                  class="text-gray-500 fw-semibold fs-7 d-block text-start ps-0"
                  >Direct link clicks</span
                >
              </div>

              <div class="d-flex align-items-center">
                <span class="text-gray-800 fw-bold fs-4 me-3">1,067</span>

                <span class="badge badge-light-success fs-base">
                  <i class="ki-outline ki-arrow-up fs-5 text-success ms-n1"></i>

                  2.6%
                </span>
              </div>
            </div>
          </div>

          <div class="separator separator-dashed my-3"></div>

          <div class="d-flex align-items-center">
            <div class="symbol symbol-30px me-5">
              <span class="symbol-label">
                <i class="ki-outline ki-tiktok fs-3 text-gray-600"></i>
              </span>
            </div>

            <div
              class="d-flex align-items-center flex-stack flex-wrap d-grid gap-1 flex-row-fluid"
            >
              <div class="me-5">
                <a
                  href="#"
                  class="text-gray-800 fw-bold text-hover-primary fs-6"
                  >Social Networks</a
                >

                <span
                  class="text-gray-500 fw-semibold fs-7 d-block text-start ps-0"
                  >All Social Channels
                </span>
              </div>

              <div class="d-flex align-items-center">
                <span class="text-gray-800 fw-bold fs-4 me-3">24,588</span>

                <span class="badge badge-light-success fs-base">
                  <i class="ki-outline ki-arrow-up fs-5 text-success ms-n1"></i>

                  4.1%
                </span>
              </div>
            </div>
          </div>

          <div class="separator separator-dashed my-3"></div>

          <div class="d-flex align-items-center">
            <div class="symbol symbol-30px me-5">
              <span class="symbol-label">
                <i class="ki-outline ki-sms fs-3 text-gray-600"></i>
              </span>
            </div>

            <div
              class="d-flex align-items-center flex-stack flex-wrap d-grid gap-1 flex-row-fluid"
            >
              <div class="me-5">
                <a
                  href="#"
                  class="text-gray-800 fw-bold text-hover-primary fs-6"
                  >Email Newsletter</a
                >

                <span
                  class="text-gray-500 fw-semibold fs-7 d-block text-start ps-0"
                  >Mailchimp Campaigns</span
                >
              </div>

              <div class="d-flex align-items-center">
                <span class="text-gray-800 fw-bold fs-4 me-3">794</span>

                <span class="badge badge-light-success fs-base">
                  <i class="ki-outline ki-arrow-up fs-5 text-success ms-n1"></i>

                  0.2%
                </span>
              </div>
            </div>
          </div>

          <div class="separator separator-dashed my-3"></div>

          <div class="d-flex align-items-center">
            <div class="symbol symbol-30px me-5">
              <span class="symbol-label">
                <i class="ki-outline ki-icon fs-3 text-gray-600"></i>
              </span>
            </div>

            <div
              class="d-flex align-items-center flex-stack flex-wrap d-grid gap-1 flex-row-fluid"
            >
              <div class="me-5">
                <a
                  href="#"
                  class="text-gray-800 fw-bold text-hover-primary fs-6"
                  >Referrals</a
                >

                <span
                  class="text-gray-500 fw-semibold fs-7 d-block text-start ps-0"
                  >Impact Radius visits</span
                >
              </div>

              <div class="d-flex align-items-center">
                <span class="text-gray-800 fw-bold fs-4 me-3">6,578</span>

                <span class="badge badge-light-danger fs-base">
                  <i
                    class="ki-outline ki-arrow-down fs-5 text-danger ms-n1"
                  ></i>

                  0.4%
                </span>
              </div>
            </div>
          </div>

          <div class="separator separator-dashed my-3"></div>

          <div class="d-flex align-items-center">
            <div class="symbol symbol-30px me-5">
              <span class="symbol-label">
                <i class="ki-outline ki-abstract-25 fs-3 text-gray-600"></i>
              </span>
            </div>

            <div
              class="d-flex align-items-center flex-stack flex-wrap d-grid gap-1 flex-row-fluid"
            >
              <div class="me-5">
                <a
                  href="#"
                  class="text-gray-800 fw-bold text-hover-primary fs-6"
                  >Other</a
                >

                <span
                  class="text-gray-500 fw-semibold fs-7 d-block text-start ps-0"
                  >Many Sources</span
                >
              </div>

              <div class="d-flex align-items-center">
                <span class="text-gray-800 fw-bold fs-4 me-3">79,458</span>

                <span class="badge badge-light-success fs-base">
                  <i class="ki-outline ki-arrow-up fs-5 text-success ms-n1"></i>

                  8.3%
                </span>
              </div>
            </div>
          </div>

          <div class="separator separator-dashed my-3"></div>

          <div class="d-flex align-items-center">
            <div class="symbol symbol-30px me-5">
              <span class="symbol-label">
                <i class="ki-outline ki-abstract-39 fs-3 text-gray-600"></i>
              </span>
            </div>

            <div
              class="d-flex align-items-center flex-stack flex-wrap d-grid gap-1 flex-row-fluid"
            >
              <div class="me-5">
                <a
                  href="#"
                  class="text-gray-800 fw-bold text-hover-primary fs-6"
                  >Rising Networks</a
                >

                <span
                  class="text-gray-500 fw-semibold fs-7 d-block text-start ps-0"
                  >Social Network</span
                >
              </div>

              <div class="d-flex align-items-center">
                <span class="text-gray-800 fw-bold fs-4 me-3">18,047</span>

                <span class="badge badge-light-success fs-base">
                  <i class="ki-outline ki-arrow-up fs-5 text-success ms-n1"></i>

                  1.9%
                </span>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
  <div class="col-xl-6 mb-5 mb-xl-10">
    <div class="card card-flush h-xl-100">
      <div class="card-header pt-5">
        <h3 class="card-title align-items-start flex-column">
          <span class="card-label fw-bold text-gray-800">Últimas Vendas</span>

          <span class="text-gray-500 pt-1 fw-semibold fs-6"
            >Counted in Millions</span
          >
        </h3>

        <div class="card-toolbar">
          <a href="#" class="btn btn-sm btn-light">PDF Report</a>
        </div>
      </div>

      <div class="card-body py-3">
        <div class="table-responsive">
          <table class="table table-row-dashed align-middle gs-0 gy-4">
            <thead>
              <tr class="fs-7 fw-bold border-0 text-gray-500">
                <th class="min-w-150px" colspan="2">CAMPAIGN</th>
                <th class="min-w-150px text-end pe-0" colspan="2">SESSIONS</th>
                <th class="text-end min-w-150px" colspan="2">
                  CONVERSION RATE
                </th>
              </tr>
            </thead>

            <tbody>
              <tr>
                <td class="" colspan="2">
                  <a
                    href="#"
                    class="text-gray-800 fw-bold text-hover-primary mb-1 fs-6"
                    >Google</a
                  >
                </td>

                <td class="pe-0" colspan="2">
                  <div class="d-flex justify-content-end">
                    <span class="text-gray-800 fw-bold fs-6 me-1">1,256</span>

                    <span
                      class="text-danger min-w-50px d-block text-end fw-bold fs-6"
                      >-935</span
                    >
                  </div>
                </td>

                <td class="" colspan="2">
                  <div class="d-flex justify-content-end">
                    <span class="text-gray-900 fw-bold fs-6 me-3">23.63%</span>

                    <span
                      class="text-danger min-w-60px d-block text-end fw-bold fs-6"
                      >-9.35%</span
                    >
                  </div>
                </td>
              </tr>
              <tr>
                <td class="" colspan="2">
                  <a
                    href="#"
                    class="text-gray-800 fw-bold text-hover-primary mb-1 fs-6"
                    >Facebook</a
                  >
                </td>

                <td class="pe-0" colspan="2">
                  <div class="d-flex justify-content-end">
                    <span class="text-gray-800 fw-bold fs-6 me-1">446</span>

                    <span
                      class="text-danger min-w-50px d-block text-end fw-bold fs-6"
                      >-576</span
                    >
                  </div>
                </td>

                <td class="" colspan="2">
                  <div class="d-flex justify-content-end">
                    <span class="text-gray-900 fw-bold fs-6 me-3">12.45%</span>

                    <span
                      class="text-danger min-w-60px d-block text-end fw-bold fs-6"
                      >-57.02%</span
                    >
                  </div>
                </td>
              </tr>
              <tr>
                <td class="" colspan="2">
                  <a
                    href="#"
                    class="text-gray-800 fw-bold text-hover-primary mb-1 fs-6"
                    >Bol.com</a
                  >
                </td>

                <td class="pe-0" colspan="2">
                  <div class="d-flex justify-content-end">
                    <span class="text-gray-800 fw-bold fs-6 me-1">67</span>

                    <span
                      class="text-success min-w-50px d-block text-end fw-bold fs-6"
                      >+24</span
                    >
                  </div>
                </td>

                <td class="" colspan="2">
                  <div class="d-flex justify-content-end">
                    <span class="text-gray-900 fw-bold fs-6 me-3">73.63%</span>

                    <span
                      class="text-success min-w-60px d-block text-end fw-bold fs-6"
                      >+28.73%</span
                    >
                  </div>
                </td>
              </tr>
              <tr>
                <td class="" colspan="2">
                  <a
                    href="#"
                    class="text-gray-800 fw-bold text-hover-primary mb-1 fs-6"
                    >Dutchnews.nl</a
                  >
                </td>

                <td class="pe-0" colspan="2">
                  <div class="d-flex justify-content-end">
                    <span class="text-gray-800 fw-bold fs-6 me-1">2,136</span>

                    <span
                      class="text-danger min-w-50px d-block text-end fw-bold fs-6"
                      >-1,229</span
                    >
                  </div>
                </td>

                <td class="" colspan="2">
                  <div class="d-flex justify-content-end">
                    <span class="text-gray-900 fw-bold fs-6 me-3">3.67%</span>

                    <span
                      class="text-danger min-w-60px d-block text-end fw-bold fs-6"
                      >-12.29%</span
                    >
                  </div>
                </td>
              </tr>
              <tr>
                <td class="" colspan="2">
                  <a
                    href="#"
                    class="text-gray-800 fw-bold text-hover-primary mb-1 fs-6"
                    >Stackoverflow</a
                  >
                </td>

                <td class="pe-0" colspan="2">
                  <div class="d-flex justify-content-end">
                    <span class="text-gray-800 fw-bold fs-6 me-1">945</span>

                    <span
                      class="text-danger min-w-50px d-block text-end fw-bold fs-6"
                      >-634</span
                    >
                  </div>
                </td>

                <td class="" colspan="2">
                  <div class="d-flex justify-content-end">
                    <span class="text-gray-900 fw-bold fs-6 me-3">25.03%</span>

                    <span
                      class="text-danger min-w-60px d-block text-end fw-bold fs-6"
                      >-9.35%</span
                    >
                  </div>
                </td>
              </tr>
              <tr>
                <td class="" colspan="2">
                  <a
                    href="#"
                    class="text-gray-800 fw-bold text-hover-primary mb-1 fs-6"
                    >Themeforest</a
                  >
                </td>

                <td class="pe-0" colspan="2">
                  <div class="d-flex justify-content-end">
                    <span class="text-gray-800 fw-bold fs-6 me-1">237</span>

                    <span
                      class="text-success min-w-50px d-block text-end fw-bold fs-6"
                      >106</span
                    >
                  </div>
                </td>

                <td class="" colspan="2">
                  <div class="d-flex justify-content-end">
                    <span class="text-gray-900 fw-bold fs-6 me-3">36.52%</span>

                    <span
                      class="text-success min-w-60px d-block text-end fw-bold fs-6"
                      >+3.06%</span
                    >
                  </div>
                </td>
              </tr>
            </tbody>
          </table>
        </div>
      </div>
    </div>
  </div>

  <div class="col-xl-6 mb-5 mb-xl-10">
    <div class="card card-flush h-xl-100">
      <div class="card-header pt-5">
        <h3 class="card-title align-items-start flex-column">
          <span class="card-label fw-bold text-gray-800"
            >Cupons mais usados</span
          >

          <span class="text-gray-500 pt-2 fw-semibold fs-6"
            >Contagem nos últimos 3 meses</span
          >
        </h3>

        <div class="card-toolbar">
          <a href="#" class="btn btn-sm btn-light">PDF Report</a>
        </div>
      </div>

      <div class="card-body d-flex justify-content-between flex-column py-3">
        <div class="m-0"></div>

        <div class="table-responsive mb-n2">
          <table class="table table-row-dashed gs-0 gy-4">
            <thead>
              <tr class="fs-7 fw-bold border-0 text-gray-500">
                <th class="min-w-300px">KEYWORD</th>
                <th class="min-w-100px">CLICKS</th>
              </tr>
            </thead>

            <tbody>
              <tr>
                <td>
                  <a
                    href="#"
                    class="text-gray-600 fw-bold text-hover-primary mb-1 fs-6"
                    >Buy phone online</a
                  >
                </td>
                <td class="d-flex align-items-center border-0">
                  <span class="fw-bold text-gray-800 fs-6 me-3">263</span>

                  <div class="progress rounded-start-0">
                    <div
                      class="progress-bar bg-success m-0"
                      role="progressbar"
                      style="height: 12px; width: 166px"
                      aria-valuenow="166"
                      aria-valuemin="0"
                      aria-valuemax="166px"
                    ></div>
                  </div>
                </td>
              </tr>
              <tr>
                <td>
                  <a
                    href="#"
                    class="text-gray-600 fw-bold text-hover-primary mb-1 fs-6"
                    >Top 10 Earbuds</a
                  >
                </td>
                <td class="d-flex align-items-center border-0">
                  <span class="fw-bold text-gray-800 fs-6 me-3">238</span>

                  <div class="progress rounded-start-0">
                    <div
                      class="progress-bar bg-success m-0"
                      role="progressbar"
                      style="height: 12px; width: 158px"
                      aria-valuenow="158"
                      aria-valuemin="0"
                      aria-valuemax="158px"
                    ></div>
                  </div>
                </td>
              </tr>
              <tr>
                <td>
                  <a
                    href="#"
                    class="text-gray-600 fw-bold text-hover-primary mb-1 fs-6"
                    >Cyber Monday</a
                  >
                </td>
                <td class="d-flex align-items-center border-0">
                  <span class="fw-bold text-gray-800 fs-6 me-3">189</span>

                  <div class="progress rounded-start-0">
                    <div
                      class="progress-bar bg-success m-0"
                      role="progressbar"
                      style="height: 12px; width: 129px"
                      aria-valuenow="129"
                      aria-valuemin="0"
                      aria-valuemax="129px"
                    ></div>
                  </div>
                </td>
              </tr>
              <tr>
                <td>
                  <a
                    href="#"
                    class="text-gray-600 fw-bold text-hover-primary mb-1 fs-6"
                    >OLED TV in Amsterdam</a
                  >
                </td>
                <td class="d-flex align-items-center border-0">
                  <span class="fw-bold text-gray-800 fs-6 me-3">263</span>

                  <div class="progress rounded-start-0">
                    <div
                      class="progress-bar bg-success m-0"
                      role="progressbar"
                      style="height: 12px; width: 112px"
                      aria-valuenow="112"
                      aria-valuemin="0"
                      aria-valuemax="112px"
                    ></div>
                  </div>
                </td>
              </tr>
              <tr>
                <td>
                  <a
                    href="#"
                    class="text-gray-600 fw-bold text-hover-primary mb-1 fs-6"
                    >Macbook M1</a
                  >
                </td>
                <td class="d-flex align-items-center border-0">
                  <span class="fw-bold text-gray-800 fs-6 me-3">263</span>

                  <div class="progress rounded-start-0">
                    <div
                      class="progress-bar bg-success m-0"
                      role="progressbar"
                      style="height: 12px; width: 107px"
                      aria-valuenow="107"
                      aria-valuemin="0"
                      aria-valuemax="107px"
                    ></div>
                  </div>
                </td>
              </tr>
              <tr>
                <td>
                  <a
                    href="#"
                    class="text-gray-600 fw-bold text-hover-primary mb-1 fs-6"
                    >Best noise cancelation headsets</a
                  >
                </td>
                <td class="d-flex align-items-center border-0">
                  <span class="fw-bold text-gray-800 fs-6 me-3">263</span>

                  <div class="progress rounded-start-0">
                    <div
                      class="progress-bar bg-success m-0"
                      role="progressbar"
                      style="height: 12px; width: 74px"
                      aria-valuenow="74"
                      aria-valuemin="0"
                      aria-valuemax="74px"
                    ></div>
                  </div>
                </td>
              </tr>
            </tbody>
          </table>
        </div>
      </div>
    </div>
  </div>
</div>
@endsection
